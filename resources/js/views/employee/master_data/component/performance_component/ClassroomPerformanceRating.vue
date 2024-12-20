<template>
  <div>
    <v-toolbar flat>
      <v-toolbar-title class="mt-2">Performance Rating - Classroom</v-toolbar-title>
      <v-divider vertical class="ma-2 ml-4" thickness="20px"></v-divider>
      <v-tooltip top v-if="hasPermission('employee-master-data-classroom-performance-rating-create')">
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
    <v-simple-table fixed-header class="tableFixHead" id="classroom_performance_ratings">
      <template v-slot:default>
        <thead>
          <tr>
            <th class="pa-2" style="width:10%">#</th>
            <th class="pa-2" style="width:35%">Department</th>
            <th class="pa-2" style="width:35%">Actual Grade(%)</th>
            <th class="pa-2" style="width:20%"> Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in classroom_performance_ratings" :class="index === editedPerfomanceIndex ? $v.editedItem.$error ? 'red lighten-5' : 'blue lighten-5' : ''">
            <td class="pa-2" style="width:10%"> {{ index + 1 }} </td>
            <!-- START Show Data if row is not for edit (show by default) -->
            <template v-if="index !== editedPerfomanceIndex && item.status !== 'New'">
              <td class="pa-2" style="width:35%"> {{ item.department }}</td>
              <td class="pa-2" style="width:35%"> {{ item.grade }} </td>
            </template>
            <!-- END Show Data if row is not for edit (show by default) -->

            <!-- START Show Fields if row is for edit -->
            <template v-if="index === editedPerfomanceIndex || item.status === 'New'">
              <td class="pa-2" style="width:35%">
                <v-autocomplete
                  v-model="editedItem.department"
                  :items="filteredDepartments"
                  item-text="name"
                  item-value="name"
                  hide-details=""
                  dense
                  :error-messages="departmentErrors"
                  @input="$v.editedItem.department.$touch()"
                  @blur="$v.editedItem.department.$touch()"
                >
                </v-autocomplete>
              </td>
              <td class="pa-2" style="width:35%">
                <v-text-field
                  name="grade"
                  v-model="editedItem.grade"
                  dense
                  hide-details
                  :error-messages="gradeErrors"
                  @input="$v.editedItem.grade.$touch()"
                  @blur="$v.editedItem.grade.$touch()"
                  @keypress="intNumValFilter()"
                ></v-text-field>
              </td>
            </template>
            <!-- END Show Fields if row is for edit -->
            
            <!-- START Show Edit(pencil icon) and Delete (trash icon) button if not Edit Mode (show by default) -->
            <template v-if="index !== editedPerfomanceIndex && item.status !== 'New' ">
              <td class="pa-2" style="width:20%">
                <v-icon
                  small
                  class="mr-2"
                  color="green"
                  @click="editItem(item)"
                  :disabled="table_action_mode === 'Add' ? true : false"
                  v-if="hasPermission('employee-master-data-classroom-performance-rating-edit')"
                >
                  mdi-pencil
                </v-icon>

                <v-icon
                  small
                  color="red"
                  @click="showConfirmAlert(item)"
                  :disabled="['Add', 'Edit'].includes(table_action_mode)"
                  v-if="hasPermission('employee-master-data-classroom-performance-rating-delete')"
                >
                  mdi-delete
                </v-icon>
              </td>
            </template>
            <!-- END  Show Edit(pencil icon) and Delete (trash icon) button if not Edit Mode (show by default) -->

            <!-- START  Show Save and Cancel button if Edit Mode -->
            <template v-if="index === editedPerfomanceIndex ? true : false || item.status === 'New' ">
              <td class="pa-2" style="width:20%">
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

  props: ['editedIndex', 'key_performances', 'data', 'departments'],

  mixins: [validationMixin],

  validations: {
    editedItem: { 
      grade: { required: requiredIf(function () {
            return this.table_action_mode;
          }),  
      },
      department: { required: requiredIf(function () {
            return this.table_action_mode;
          }),  
      },
    },
    period: { required },
  },
  data() {
    return {
      editedPerfomanceIndex: -1,
      editedItem: {
        department: "",
        grade: "",
      },
      defaultField: {
        department: "",
        grade: "",
      },
      classroom_performance_ratings: [],
      added_classroom_performance_ratings: [],
      deleted_classroom_performance_ratings: [],
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
      
      this.classroom_performance_ratings.forEach((value, index) => {
        if (value.status === "New") {
          hasNew = true;
        }
      });

      if (!hasNew) {
        this.classroom_performance_ratings.push({ status: "New" });
      }
      
      setTimeout(() => {
        let container = this.$el.querySelector("#classroom_performance_ratings tbody");
        container.scrollTop = container.scrollHeight;
      }, 1);

    },
    storePerformance() {

      let data = Object.assign(this.editedItem, { employee_id: this.data.id });
      
      axios.post("/api/employee_master_data/classroom_performance_rating/store", data).then(
        (response) => {
          this.loading = false;
          let data = response.data;
          
          if(data.success)
          {
            this.classroom_performance_ratings = data.performances;
            this.$emit('updateClassroomPerformanceRating', data.performances);
            this.showAlert(data.success);
          }
          
          // reset array
          this.added_classroom_performance_ratings = [];
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    updatePerformance() {

      let data = { department: this.editedItem.department, grade: this.editedItem.grade };
      axios.post("/api/employee_master_data/classroom_performance_rating/update/"+this.editedItem.id, data).then(
        (response) => {
          this.loading = false;
          let data = response.data;
          
          if(data.success)
          {
            this.classroom_performance_ratings = data.performances;
            this.$emit('updateClassroomPerformanceRating', data.performances);
            this.showAlert(data.success);
          }
          
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    deletePerformance(item) {
      
      this.loading = true;
      let data = { performance_id: item.id };
      axios.post("/api/employee_master_data/classroom_performance_rating/delete", data).then(
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

      if(!this.$v.editedItem.$error && !this.gradeErrors.length)
      {
        if(this.table_action_mode === 'Add')
        {
          let index = this.classroom_performance_ratings.indexOf({ status: 'New' }); 
          this.classroom_performance_ratings.splice(index, 1);

          // edit mode; from parent component
          if(this.editedIndex > -1)
          {
            this.storePerformance();
          }
          else
          {
            this.classroom_performance_ratings.push(this.editedItem);
          }

        }
        else
        {
          // edit mode; from parent component
          if(this.editedIndex > -1)
          {
            this.updatePerformance();
          }
          else
          {
            this.classroom_performance_ratings[this.editedPerfomanceIndex] = this.editedItem;
          }
          
        }

        this.resetData();
      }
      
    },

    cancelEvent(item) {
      this.editedPerfomanceIndex = this.classroom_performance_ratings.indexOf(item);
      if (this.table_action_mode === "Add") {
        this.classroom_performance_ratings.splice(this.editedPerfomanceIndex, 1);
      } 

      this.resetData();
    },

    editItem(item) {
      this.table_action_mode = "Edit";
      this.editedItem = Object.assign({}, item);
      this.editedPerfomanceIndex = this.classroom_performance_ratings.indexOf(item);
    },

    resetData(){
      this.$v.editedItem.$reset();
      this.editedItem = Object.assign({}, this.defaultField);
      this.editedPerfomanceIndex = -1;
      this.table_action_mode = "";
    },

    clear() {
      this.resetData();
      this.classroom_performance_ratings = [];
      this.added_classroom_performance_ratings = [];
      this.deleted_classroom_performance_ratings = [];
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
            
            this.deletePerformance(item);
            
          }
        });
      }
      else
      {
        this.removeItem();
      }

      
    },

    removeItem() {
      let index = this.classroom_performance_ratings.indexOf(this.editedItem);
      this.classroom_performance_ratings.splice(index, 1);
    },

    intNumValFilter(evt) {
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
  watch: {
    'editedItem.department'() {
      console.log(this.editedItem.department);
      
    },
  },
  computed: {
    departmentErrors() {
      const errors = [];
      if (!this.$v.editedItem.department.$dirty) return errors;
      !this.$v.editedItem.department.required &&
        errors.push("Department is required.");
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

    filteredDepartments() {

      let departments = [];
      
      this.departments.forEach(department => {

        let index = this.classroom_performance_ratings.findIndex((value) => value.department == department.name);
        
        if(index < 0)
        {
          departments.push(department);  
        }
      
      });

      // if row edit mode then add the edited value into selection
      if(this.table_action_mode == 'Edit')
      {
        let filtered_departments = this.departments.filter(value => value.name == this.editedItem.department);
        let department = filtered_departments.length ? filtered_departments[0] : '';

        departments.unshift(department);
      }

      return departments;
    },

    ...mapGetters("auth", ["isUnauthorized"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),

  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    
    if(this.editedIndex > -1)
    {
      this.classroom_performance_ratings = this.data.classroom_performance_ratings;
    }
  },
};
</script>
