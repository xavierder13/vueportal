<template>
  <div>
    <v-toolbar flat>
      <v-toolbar-title class="mt-2">Training & Re-Development</v-toolbar-title>
      <v-divider vertical class="ma-2 ml-4" thickness="20px"></v-divider>
      <v-tooltip top v-if="hasPermission('employee-master-data-training-create')">
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
    <v-simple-table fixed-header class="tableFixHead" id="trainings">
      <template v-slot:default>
        <thead>
          <tr>
            <th class="pa-2" style="width:5%">#</th>
            <th class="pa-2" style="width:25%">Mentor</th>
            <th class="pa-2" style="width:15%">Grade(%)</th>
            <th class="pa-2" style="width:15%">KPI(%)</th>
            <th class="pa-2" style="width:30%">Remarks</th>
            <th class="pa-2" style="width:10%"> Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in trainings" :class="index === editedTrainingIndex ? $v.editedItem.$error ? 'red lighten-5' : 'blue lighten-5' : ''">
            <td class="pa-2" style="width:5%"> {{ index + 1 }} </td>
            <!-- START Show Data if row is not for edit (show by default) -->
            <template v-if="index !== editedTrainingIndex && item.status !== 'New'">
              <td class="pa-2" style="width:25%"> {{ item.mentor }}</td>
              <td class="pa-2" style="width:15%"> {{ item.grade }} </td>
              <td class="pa-2" style="width:15%"> {{ item.kpi }} </td>
              <td class="pa-2" style="width:30%"> {{ item.remarks }} </td>
            </template>
            <!-- END Show Data if row is not for edit (show by default) -->

            <!-- START Show Fields if row is for edit -->
            <template v-if="index === editedTrainingIndex || item.status === 'New'">
              <td class="pa-2" style="width:25%">
                <v-text-field
                  name="mentor"
                  v-model="editedItem.mentor"
                  dense
                  hide-details
                  :error-messages="mentorErrors"
                  @input="$v.editedItem.mentor.$touch()"
                  @blur="$v.editedItem.mentor.$touch()"
                ></v-text-field>
              </td>
              <td class="pa-2" style="width:15%">
                <v-text-field
                  name="grade"
                  v-model="editedItem.grade"
                  dense
                  hide-details
                  :error-messages="gradeErrors"
                  @input="$v.editedItem.grade.$touch()"
                  @blur="$v.editedItem.grade.$touch()"
                  @keypress="decNumValFilter()"
                ></v-text-field>
              </td>
              <td class="pa-2" style="width:15%">
                <v-text-field
                  name="kpi"
                  v-model="editedItem.kpi"
                  dense
                  hide-details
                  :error-messages="kpiErrors"
                  @input="$v.editedItem.kpi.$touch()"
                  @blur="$v.editedItem.kpi.$touch()"
                  @keypress="decNumValFilter()"
                ></v-text-field>
              </td>
              <td class="pa-2" style="width:30%">
                <v-text-field
                  name="remarks"
                  v-model="editedItem.remarks"
                  dense
                  hide-details
                ></v-text-field>
              </td>
            </template>
            <!-- END Show Fields if row is for edit -->
            
            <!-- START Show Edit(pencil icon) and Delete (trash icon) button if not Edit Mode (show by default) -->
            <template v-if="index !== editedTrainingIndex && item.status !== 'New' ">
              <td class="pa-2" style="width:10%">
                <v-icon
                  small
                  class="mr-2"
                  color="green"
                  @click="editItem(item)"
                  :disabled="table_action_mode === 'Add' ? true : false"
                  v-if="hasPermission('employee-master-data-training-edit')"
                >
                  mdi-pencil
                </v-icon>

                <v-icon
                  small
                  color="red"
                  @click="showConfirmAlert(item)"
                  :disabled="['Add', 'Edit'].includes(table_action_mode)"
                  v-if="hasPermission('employee-master-data-training-delete')"
                >
                  mdi-delete
                </v-icon>
              </td>
            </template>
            <!-- END  Show Edit(pencil icon) and Delete (trash icon) button if not Edit Mode (show by default) -->

            <!-- START  Show Save and Cancel button if Edit Mode -->
            <template v-if="index === editedTrainingIndex ? true : false || item.status === 'New' ">
              <td class="pa-2" style="width:10%">
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

  props: ['editedIndex', 'key_trainings', 'data', 'departments'],

  mixins: [validationMixin],

  validations: {
    editedItem: { 
      mentor: { required: requiredIf(function () {
            return this.table_action_mode;
          }),  
      },
      grade: { required: requiredIf(function () {
            return this.table_action_mode;
          }),  
      },
      kpi: { required: requiredIf(function () {
            return this.table_action_mode;
          }),  
      },
      
    },
    period: { required },
  },
  data() {
    return {
      editedTrainingIndex: -1,
      editedItem: {
        mentor: "",
        grade: "",
        kpi: "",
        remarks: "",
      },
      defaultField: {
        mentor: "",
        grade: "",
        kpi: "",
        remarks: "",
      },
      trainings: [],
      added_trainings: [],
      deleted_trainings: [],
      action_mode: "",
      table_action_mode: "",
      disabled: false,
      addedItems: [],
      deletedItems: [],
    };
  },

  methods: {
    newItem() {
      this.resetData();
      this.table_action_mode = "Add";

      let hasNew = false;
      
      this.trainings.forEach((value, index) => {
        if (value.status === "New") {
          hasNew = true;
        }
      });

      if (!hasNew) {
        this.trainings.push({ status: "New" });
      }
      
      setTimeout(() => {
        let container = this.$el.querySelector("#trainings tbody");
        container.scrollTop = container.scrollHeight;
      }, 1);

    },
    storeTraining() {

      let data = Object.assign(this.editedItem, { employee_id: this.data.id });
      
      axios.post("/api/employee_master_data/training/store", data).then(
        (response) => {
          this.loading = false;
          let data = response.data;
          
          if(data.success)
          {
            this.trainings = data.trainings;
            this.$emit('updateTraining', data.trainings);
            this.showAlert(data.success);
          }
          
          // reset array
          this.added_trainings = [];
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    updateTraining() {

      let data = this.editedItem;

      axios.post("/api/employee_master_data/training/update/"+this.editedItem.id, data).then(
        (response) => {
          this.loading = false;
          let data = response.data;
          
          if(data.success)
          {
            this.trainings = data.trainings;
            this.$emit('updateTraining', data.trainings);
            this.showAlert(data.success);
          }
          
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    deleteTraining(item) {
      
      this.loading = true;
      let data = { training_id: item.id };
      axios.post("/api/employee_master_data/training/delete", data).then(
        (response) => {
          this.loading = false;

          let data = response.data;
          if(data.success)
          {
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

      if(!this.$v.editedItem.$error && !this.gradeErrors.length && !this.kpiErrors.length)
      {
        if(this.table_action_mode === 'Add')
        {
          let index = this.trainings.indexOf({ status: 'New' }); 
          this.trainings.splice(index, 1);
          
          // edit mode; from parent component
          if(this.editedIndex > -1)
          {
            this.storeTraining();
          }
          else
          {
            this.trainings.push(this.editedItem);
          }

        }
        else
        {
          // edit mode; from parent component
          if(this.editedIndex > -1)
          {
            this.updateTraining();
          }
          else
          {
            this.trainings[this.editedTrainingIndex] = this.editedItem;
          }
          
        }

        this.resetData();
      }
      
    },

    cancelEvent(item) {
      this.editedTrainingIndex = this.trainings.indexOf(item);
      if (this.table_action_mode === "Add") {
        this.trainings.splice(this.editedTrainingIndex, 1);
      } 

      this.resetData();
    },

    editItem(item) {
      this.table_action_mode = "Edit";
      this.editedItem = Object.assign({}, item);
      this.editedTrainingIndex = this.trainings.indexOf(item);
    },

    resetData(){
      this.$v.editedItem.$reset();
      this.editedItem = Object.assign({}, this.defaultField);
      this.editedTrainingIndex = -1;
      this.table_action_mode = "";
    },

    clear() {
      this.resetData();
      this.trainings = [];
      this.added_trainings = [];
      this.deleted_trainings = [];
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
            
            this.deleteTraining(item);
            
          }
        });
      }
      else
      {
        this.removeItem();
      }

      
    },

    removeItem() {
      let index = this.trainings.indexOf(this.editedItem);
      this.trainings.splice(index, 1);
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

  },
  computed: {
    mentorErrors() {
      const errors = [];
      if (!this.$v.editedItem.mentor.$dirty) return errors;
      !this.$v.editedItem.mentor.required &&
        errors.push("Mentor is required.");
      return errors;
    },
    
    gradeErrors(){
      const errors = [];
      if (!this.$v.editedItem.grade.$dirty) return errors;
      !this.$v.editedItem.grade.required && errors.push("Grade is required.");

      if(parseInt(this.editedItem.grade) < 0 || parseInt(this.editedItem.grade) > 999999.9)
      {
        errors.push("Invalid Value");
      }

      return errors;
    },

    kpiErrors(){
      const errors = [];
      if (!this.$v.editedItem.kpi.$dirty) return errors;
      !this.$v.editedItem.kpi.required && errors.push("KPI is required.");

      if(parseInt(this.editedItem.kpi) < 0 || parseInt(this.editedItem.kpi) > 999999.9)
      {
        errors.push("Invalid Value");
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
      this.trainings = this.data.trainings;
    }
  },
};
</script>
