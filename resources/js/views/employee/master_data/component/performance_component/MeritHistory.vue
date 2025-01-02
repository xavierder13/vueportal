<template>
  <div>
    <v-toolbar flat>
      <v-toolbar-title class="mt-2">Merit History</v-toolbar-title>
      <v-divider vertical class="ma-2 ml-4" thickness="20px"></v-divider>
      <v-tooltip top v-if="hasPermission('employee-master-data-merit-history-create')">
        <template v-slot:activator="{ on, attrs }">
          <v-btn 
            small 
            class="mx-2 mt-2" 
            color="primary" 
            rounded 
            fab
            v-bind="attrs" v-on="on"
            @click="newItem()" 
            :disabled="['Add', 'Edit'].includes(table_action_mode)"
          >
              <v-icon>mdi-plus</v-icon> 
          </v-btn>
        </template>
        
        <span>Add Period</span>
      </v-tooltip>
    </v-toolbar>
    <v-simple-table fixed-header class="tableFixHead" id="merit_histories">
      <template v-slot:default>
        <thead>
          <tr>
            <th class="pa-2" style="width:5%">#</th>
            <th class="pa-2" style="width:40%">Merit Date</th>
            <th class="pa-2" style="width:40%">Salary</th>
            <th class="pa-2" style="width:15%"> Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in merit_histories" :class="index === editedMeritHistoryIndex ? $v.editedItem.$error ? 'red lighten-5' : 'blue lighten-5' : ''">
            <td class="pa-2" style="width:5%"> {{ index + 1 }} </td>
            <!-- START Show Data if row is not for edit (show by default) -->
            <template v-if="index !== editedMeritHistoryIndex && item.status !== 'New'">
              <td class="pa-2" style="width:40%"> {{ formatDate(item.merit_date) }}</td>
              <td class="pa-2" style="width:40%"> {{ item.salary }} </td>
            </template>
            <!-- END Show Data if row is not for edit (show by default) -->

            <!-- START Show Fields if row is for edit -->
            <template v-if="index === editedMeritHistoryIndex || item.status === 'New'">
              <td class="pa-2" style="width:40%">
                <v-text-field
                  type="date"
                  v-model="editedItem.merit_date"
                  dense
                  hide-details
                  @input="$v.editedItem.merit_date.$touch() + validateDate('merit_date')"
                  @blur="$v.editedItem.merit_date.$touch()"
                ></v-text-field>
              </td>
              <td class="pa-2" style="width:40%">
                <v-text-field
                  name="salary"
                  v-model="editedItem.salary"
                  dense
                  hide-details
                  @keypress="decNumValFilter()"
                ></v-text-field>
              </td>
            </template>
            <!-- END Show Fields if row is for edit -->
            
            <!-- START Show Edit(pencil icon) and Delete (trash icon) button if not Edit Mode (show by default) -->
            <template v-if="index !== editedMeritHistoryIndex && item.status !== 'New' ">
              <td class="pa-2" style="width:15%">
                <v-icon
                  small
                  class="mr-2"
                  color="green"
                  @click="editItem(item)"
                  :disabled="table_action_mode === 'Add' ? true : false"
                  v-if="hasPermission('employee-master-data-merit-history-edit')"
                >
                  mdi-pencil
                </v-icon>

                <v-icon
                  small
                  color="red"
                  @click="showConfirmAlert(item)"
                  :disabled="['Add', 'Edit'].includes(table_action_mode)"
                  v-if="hasPermission('employee-master-data-merit-history-delete')"
                >
                  mdi-delete
                </v-icon>
              </td>
            </template>
            <!-- END  Show Edit(pencil icon) and Delete (trash icon) button if not Edit Mode (show by default) -->

            <!-- START  Show Save and Cancel button if Edit Mode -->
            <template v-if="index === editedMeritHistoryIndex ? true : false || item.status === 'New' ">
              <td class="pa-2" style="width:15%">
                <v-btn
                  x-small
                  :disabled="disabled"
                  @click="saveItem()"
                  icon
                >
                  <v-icon color="primary">mdi-content-save</v-icon>
                </v-btn>
                <v-btn
                  x-small
                  color="#E0E0E0"
                  @click="cancelEvent(item)"
                  icon
                >
                  <v-icon color="red">mdi-cancel</v-icon>
                </v-btn>
              </td>
            </template>
            <!-- END  Show Save and Cancel button if Edit Mode -->
          </tr>
        </tbody>
        <!-- <tfoot>
          <tr>
            <td colspan="9" class="text-right">
              <v-btn class="primary" x-small @click="newItem()" :disabled="['Add', 'Edit'].includes(table_action_mode)">add item</v-btn>
            </td>
          </tr>
        </tfoot> -->
      </template>
    </v-simple-table>
  </div>
</template>
<style scoped>
  .full-height {
    height: calc(85vh - 130px); /* Adjust 270px to suits your needs */
    overflow-y: auto;
    overflow-x: hidden;
  }

  table {
    width: 100%;
  }

  thead, tbody, tr, td, th { display: block; }

  tr:after {
      content: ' ';
      display: block;
      visibility: hidden;
      clear: both;
  }

  tbody {
      height: calc(65vh - 135px);
      overflow-y: auto;
  }

  tbody td, thead th {
      float: left;
  }

</style>
<script>

import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, requiredIf, email } from "vuelidate/lib/validators";
import { mapGetters } from "vuex";

export default {

  props: ['editedIndex', 'data'],

  mixins: [validationMixin],

  validations: {
    editedItem: { 
      merit_date: { required: requiredIf(function () {
            return this.table_action_mode;
          }),  
      },
      
    },
    period: { required },
  },
  data() {
    return {
      editedMeritHistoryIndex: -1,
      editedItem: {
        merit_date: "",
        salary: "",
      },
      defaultField: {
        merit_date: "",
        salary: "",
      },
      merit_histories: [],
      added_merit_histories: [],
      deleted_merit_histories: [],
      action_mode: "",
      table_action_mode: "",
      disabled: false,
      addedItems: [],
      deletedItems: [],
      dateErrors: {
        merit_date: { status: false, msg: "" },
      },
    };
  },

  methods: {
    newItem() {
      this.resetData();
      this.table_action_mode = "Add";

      let hasNew = false;
      
      this.merit_histories.forEach((value, index) => {
        if (value.status === "New") {
          hasNew = true;
        }
      });

      if (!hasNew) {
        this.merit_histories.push({ status: "New" });
      }
      
      setTimeout(() => {
        let container = this.$el.querySelector("#merit_histories tbody");
        container.scrollTop = container.scrollHeight;
      }, 1);

    },
    storeMeritHistory() {

      let data = Object.assign(this.editedItem, { employee_id: this.data.id });
      
      axios.post("/api/employee_master_data/merit_history/store", data).then(
        (response) => {
          this.loading = false;
          let data = response.data;

          console.log(data);
          
          
          if(data.success)
          {
            this.merit_histories = data.merit_histories;
            this.$emit('updateMeritHistory', data.merit_histories);
            this.showAlert(data.success);
          }
          
          // reset array
          this.added_merit_histories = [];
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    updateMeritHistory() {

      let data = Object.assign(this.editedItem, { employee_id: this.data.id });

      axios.post("/api/employee_master_data/merit_history/update/"+this.editedItem.id, data).then(
        (response) => {
          this.loading = false;
          let data = response.data;

          console.log(data);
          
          
          if(data.success)
          {
            this.merit_histories = data.merit_histories;
            this.$emit('updateMeritHistory', data.merit_histories);
            this.showAlert(data.success);
          }
          
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    deleteMeritHistory(item) {
      
      this.loading = true;
      let data = { merit_history_id: item.id };
      axios.post("/api/employee_master_data/merit_history/delete", data).then(
        (response) => {
          this.loading = false;

          let data = response.data;
          if(data.success)
          {
            this.$emit('updateMeritHistory', data.merit_histories);
            this.showAlert(data.success);
            this.removeItem();
          }
          else
          {
            this.showErrorAlert(data.error);
          }
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    saveItem(){
 
      this.$v.editedItem.$touch();
      let dateModelHasErrors = Object.values(this.dateErrors).map((obj) => obj.status).includes(true);

      if(!this.$v.editedItem.$error && !dateModelHasErrors)
      {
        
        if(this.table_action_mode === 'Add')
        {
          let index = this.merit_histories.indexOf({ status: 'New' }); 
          this.merit_histories.splice(index, 1);
          
          // edit mode; from parent component
          if(this.editedIndex > -1)
          {
            this.storeMeritHistory();
          }
          else
          {
            this.merit_histories.push(this.editedItem);
          }

        }
        else
        {
          // edit mode; from parent component
          if(this.editedIndex > -1)
          {
            this.updateMeritHistory();
          }
          else
          {
            this.merit_histories[this.editedMeritHistoryIndex] = this.editedItem;
          }
          
        }

        this.resetData();
      }
      
    },

    cancelEvent(item) {
      this.editedMeritHistoryIndex = this.merit_histories.indexOf(item);
      if (this.table_action_mode === "Add") {
        this.merit_histories.splice(this.editedMeritHistoryIndex, 1);
      } 

      this.resetData();
    },

    editItem(item) {
      this.table_action_mode = "Edit";
      this.editedItem = Object.assign({}, item);
      this.editedMeritHistoryIndex = this.merit_histories.indexOf(item);
    },

    resetData(){
      this.$v.editedItem.$reset();
      this.editedItem = Object.assign({}, this.defaultField);
      this.editedMeritHistoryIndex = -1;
      this.table_action_mode = "";
    },

    clear() {
      this.resetData();
      this.merit_histories = [];
      this.added_merit_histories = [];
      this.deleted_merit_histories = [];
      this.addedItems = [];
      this.deletedItems = [];

    },

    showAlert(msg) {
      this.$swal({
        position: "center",
        icon: "success",
        title: msg,
        showConfirmButton: false,
        timer: 2500,
      });
    },

    showErrorAlert(msg) {     
      this.$swal({
        position: "center",
        icon: "error",
        title: msg,
        showConfirmButton: false,
        timer: 2500,
      });
    },

    showConfirmAlert(item) {
   
      if(this.editedIndex > -1)
      {
        this.$swal({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#d33",
          cancelButtonColor: "#6c757d",
          confirmButtonText: "Delete record!",
        }).then((result) => {
          // <--

          if (result.value) {
            
            this.deleteMeritHistory(item);
            
          }
        });
      }
      else
      {
        this.removeItem();
      }

    },

    removeItem() {
      let index = this.merit_histories.indexOf(this.editedItem);
      this.merit_histories.splice(index, 1);
    },

    decNumValFilter(evt) {
      evt = (evt) ? evt : window.event;
      let value = evt.target.value.toString() + evt.key.toString();

      if (!/^[-+]?[0-9]*\.?[0-9]*$/.test(value)) {
        evt.preventDefault();
      }
      else if(value.indexOf(".") > -1)
      {
        let split_val = value.split('.');
        let whole_num = split_val[0];
        let decimal_places = split_val[1];
        let whole_num_length = whole_num.length  
        let decimal_length = decimal_places.length;
  
        if(decimal_length > 2) //decimal places limit 2
        {
          evt.preventDefault();
        }

      } else {

        return true;
      }
    },

    validateDate(field) {
      
      // if field is set for validation
      if(this.$v.editedItem[field])
      {
        this.$v.editedItem[field].$touch();
      }

      let min_date = new Date('1900-01-01').getTime();
      let max_date = new Date().getTime();
      let date = this.editedItem[field];
   
      if(date)
      {
        let date_value = new Date(date).getTime();
        let [year, month, day] = date.split('-');

        this.dateErrors[field].status = false;
        this.dateErrors[field].msg = "";

        // if (date_value < min_date || date_value > max_date || year.length > 4) {
        if (date_value > max_date || year.length > 4 || year < 1900) {
          this.dateErrors[field].status = true;
          this.dateErrors[field].msg = 'Enter a valid date';
        }  
      }
  
    },

    formatDate(date) {
      if (!date) return null;
      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },

  },
  computed: {
    meritDateErrors() {
      const errors = [];
      if (!this.$v.editedItem.merit_date.$dirty) return errors;
      !this.$v.editedItem.merit_date.required && errors.push("Date Assigned is required.");

      if(this.dateErrors.merit_date.msg)
      {
        errors.push(this.dateErrors.merit_date.msg);
      }

      return errors;
    },

    ...mapGetters("auth", ["isUnauthorized"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),

  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    
    if(this.editedIndex > -1)
    {
      this.merit_histories = this.data.merit_histories;
    }
  },
};
</script>
