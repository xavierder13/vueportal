<template>
  <div>
    <v-toolbar flat>
      <v-toolbar-title class="mt-2">Branch Assignment & Positions</v-toolbar-title>
      <v-divider vertical class="ma-2 ml-4" thickness="20px"></v-divider>
      <v-tooltip top v-if="hasPermission('employee-master-data-branch-assignment-position-create')">
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
    <v-simple-table fixed-header class="tableFixHead" id="branch_assignment_positions">
      <template v-slot:default>
        <thead>
          <tr>
            <th class="pa-2" style="width:3%">#</th>
            <th class="pa-2" style="width:15%">Date Assigned</th>
            <th class="pa-2" style="width:20%">Position</th>
            <th class="pa-2" style="width:20%">Branch</th>
            <th class="pa-2" style="width:30%">Remarks</th>
            <th class="pa-2" style="width:10%"> Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in branch_assignment_positions" :class="index === editedBranchAssignmentIndex ? $v.editedItem.$error ? 'red lighten-5' : 'blue lighten-5' : ''">
            <td class="pa-2" style="width:3%"> {{ index + 1 }} </td>
            <!-- START Show Data if row is not for edit (show by default) -->
            <template v-if="index !== editedBranchAssignmentIndex && item.status !== 'New'">
              <td class="pa-2" style="width:15%"> {{ formatDate(item.date_assigned) }}</td>
              <td class="pa-2" style="width:20%"> {{ item.position }} </td>
              <td class="pa-2" style="width:20%"> {{ item.branch }} </td>
              <td class="pa-2" style="width:30%"> {{ item.remarks }} </td>
            </template>
            <!-- END Show Data if row is not for edit (show by default) -->

            <!-- START Show Fields if row is for edit -->
            <template v-if="index === editedBranchAssignmentIndex || item.status === 'New'">
              <td class="pa-2" style="width:15%">
                <v-text-field
                  label="Date Assigned"
                  type="date"
                  v-model="editedItem.date_assigned"
                  :error-messages="dateAssignedErrors"
                  @input="$v.editedItem.date_assigned.$touch() + validateDate('date_assigned')"
                  @blur="$v.editedItem.date_assigned.$touch()"
                ></v-text-field>
              </td>
              <td class="pa-2" style="width:20%">
                <v-autocomplete
                  v-model="editedItem.position"
                  :items="positions"
                  item-text="name"
                  item-value="name"
                  label="Position"
                  :error-messages="positionErrors"
                  @input="$v.editedItem.position.$touch()"
                  @blur="$v.editedItem.position.$touch()"
                >
                </v-autocomplete>
              </td>
              <td class="pa-2" style="width:20%">
                <v-autocomplete
                  v-model="editedItem.branch"
                  :items="branches"
                  item-text="name"
                  item-value="name"
                  label="Branch"
                  :error-messages="branchErrors"
                  @input="$v.editedItem.branch.$touch()"
                  @blur="$v.editedItem.branch.$touch()"
                >
                </v-autocomplete>
              </td>
              <td class="pa-2" style="width:30%">
                <v-text-field
                  label="Remarks"
                  v-model="editedItem.remarks"
                  :error-messages="remarksErrors"
                  @input="$v.editedItem.remarks.$touch()"
                  @blur="$v.editedItem.remarks.$touch()"
                ></v-text-field>
              </td>
            </template>
            <!-- END Show Fields if row is for edit -->
            
            <!-- START Show Edit(pencil icon) and Delete (trash icon) button if not Edit Mode (show by default) -->
            <template v-if="index !== editedBranchAssignmentIndex && item.status !== 'New' ">
              <td class="pa-2" style="width:10%">
                <v-icon
                  small
                  class="mr-2"
                  color="green"
                  @click="editItem(item)"
                  :disabled="table_action_mode === 'Add' ? true : false"
                  v-if="hasPermission('employee-master-data-branch-assignment-position-edit')"
                >
                  mdi-pencil
                </v-icon>

                <v-icon
                  small
                  color="red"
                  @click="showConfirmAlert(item)"
                  :disabled="['Add', 'Edit'].includes(table_action_mode)"
                  v-if="hasPermission('employee-master-data-branch-assignment-position-delete')"
                >
                  mdi-delete
                </v-icon>
              </td>
            </template>
            <!-- END  Show Edit(pencil icon) and Delete (trash icon) button if not Edit Mode (show by default) -->

            <!-- START  Show Save and Cancel button if Edit Mode -->
            <template v-if="index === editedBranchAssignmentIndex ? true : false || item.status === 'New' ">
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

  props: ['editedIndex', 'data', 'positions', 'branches'],

  mixins: [validationMixin],

  validations: {
    editedItem: { 
      date_assigned: { required: requiredIf(function () {
            return this.table_action_mode;
          }),  
      },
      position: { required: requiredIf(function () {
            return this.table_action_mode;
          }),  
      },
      branch: { required: requiredIf(function () {
            return this.table_action_mode;
          }),  
      },
      remarks: { required: requiredIf(function () {
            return this.table_action_mode;
          }),  
      },
      
    },
    period: { required },
  },
  data() {
    return {
      editedBranchAssignmentIndex: -1,
      editedItem: {
        date_assigned: "",
        position: "",
        branch: "",
        remarks: "",
      },
      defaultField: {
        date_assigned: "",
        position: "",
        branch: "",
        remarks: "",
      },
      branch_assignment_positions: [],
      added_branch_assignment_positions: [],
      deleted_branch_assignment_positions: [],
      action_mode: "",
      table_action_mode: "",
      disabled: false,
      addedItems: [],
      deletedItems: [],
      dateErrors: {
        date_assigned: { status: false, msg: "" },
      },
    };
  },

  methods: {
    newItem() {
      this.resetData();
      this.table_action_mode = "Add";

      let hasNew = false;
      
      this.branch_assignment_positions.forEach((value, index) => {
        if (value.status === "New") {
          hasNew = true;
        }
      });

      if (!hasNew) {
        this.branch_assignment_positions.push({ status: "New" });
      }
      
      setTimeout(() => {
        let container = this.$el.querySelector("#branch_assignment_positions tbody");
        container.scrollTop = container.scrollHeight;
      }, 1);

    },
    storeBranchAssignment() {

      let data = Object.assign(this.editedItem, { employee_id: this.data.id });
      
      axios.post("/api/employee_master_data/branch_assignment_position/store", data).then(
        (response) => {
          this.loading = false;
          let data = response.data;

          console.log(data);
          
          
          if(data.success)
          {
            this.branch_assignment_positions = data.branch_assignment_positions;
            this.$emit('updateBranchAssignmentPosition', data.branch_assignment_positions);
            this.showAlert(data.success);
          }
          
          // reset array
          this.added_branch_assignment_positions = [];
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    updateBranchPosition() {

      let data = Object.assign(this.editedItem, { employee_id: this.data.id });

      axios.post("/api/employee_master_data/branch_assignment_position/update/"+this.editedItem.id, data).then(
        (response) => {
          this.loading = false;
          let data = response.data;

          console.log(data);
          
          
          if(data.success)
          {
            this.branch_assignment_positions = data.branch_assignment_positions;
            this.$emit('updateBranchAssignmentPosition', data.branch_assignment_positions);
            this.showAlert(data.success);
          }
          
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    deleteBranchAssignment(item) {
      
      this.loading = true;
      let data = { branch_assignment_id: item.id };
      axios.post("/api/employee_master_data/branch_assignment_position/delete", data).then(
        (response) => {
          this.loading = false;

          let data = response.data;
          if(data.success)
          {
            this.$emit('updateBranchAssignmentPosition', data.branch_assignment_positions);
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
          let index = this.branch_assignment_positions.indexOf({ status: 'New' }); 
          this.branch_assignment_positions.splice(index, 1);
          
          // edit mode; from parent component
          if(this.editedIndex > -1)
          {
            this.storeBranchAssignment();
          }
          else
          {
            this.branch_assignment_positions.push(this.editedItem);
          }

        }
        else
        {
          // edit mode; from parent component
          if(this.editedIndex > -1)
          {
            this.updateBranchPosition();
          }
          else
          {
            this.branch_assignment_positions[this.editedBranchAssignmentIndex] = this.editedItem;
          }
          
        }

        this.resetData();
      }
      
    },

    cancelEvent(item) {
      this.editedBranchAssignmentIndex = this.branch_assignment_positions.indexOf(item);
      if (this.table_action_mode === "Add") {
        this.branch_assignment_positions.splice(this.editedBranchAssignmentIndex, 1);
      } 

      this.resetData();
    },

    editItem(item) {
      this.table_action_mode = "Edit";
      this.editedItem = Object.assign({}, item);
      this.editedBranchAssignmentIndex = this.branch_assignment_positions.indexOf(item);
    },

    resetData(){
      this.$v.editedItem.$reset();
      this.editedItem = Object.assign({}, this.defaultField);
      this.editedBranchAssignmentIndex = -1;
      this.table_action_mode = "";
    },

    clear() {
      this.resetData();
      this.branch_assignment_positions = [];
      this.added_branch_assignment_positions = [];
      this.deleted_branch_assignment_positions = [];
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
            
            this.deleteBranchAssignment(item);
            
          }
        });
      }
      else
      {
        this.removeItem();
      }

      
    },

    removeItem() {
      let index = this.branch_assignment_positions.indexOf(this.editedItem);
      this.branch_assignment_positions.splice(index, 1);
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
    dateAssignedErrors() {
      const errors = [];
      if (!this.$v.editedItem.date_assigned.$dirty) return errors;
      !this.$v.editedItem.date_assigned.required && errors.push("Date Assigned is required.");

      if(this.dateErrors.date_assigned.msg)
      {
        errors.push(this.dateErrors.date_assigned.msg);
      }

      return errors;
    },

    positionErrors() {
      const errors = [];
      if (!this.$v.editedItem.position.$dirty) return errors;
      !this.$v.editedItem.position.required &&
        errors.push("Position is required.");
      return errors;
    },

    branchErrors() {
      const errors = [];
      if (!this.$v.editedItem.branch.$dirty) return errors;
      !this.$v.editedItem.branch.required &&
        errors.push("Branch is required.");
      return errors;
    },

    remarksErrors() {
      const errors = [];
      if (!this.$v.editedItem.remarks.$dirty) return errors;
      !this.$v.editedItem.remarks.required &&
        errors.push("Remarks is required.");
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
      this.branch_assignment_positions = this.data.branch_assignment_positions;
    }
  },
};
</script>
