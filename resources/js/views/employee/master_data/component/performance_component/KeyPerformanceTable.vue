<template>
  <div>
    <v-simple-table class="elevation-1" id="monthly_key_performances" style="max-height: 250px; overflow-y: scroll; overflow-y: auto !important">
      <template v-slot:default>
        <thead>
          <tr>
            <th class="pa-2" width="10px">#</th>
            <th class="pa-2">Year</th>
            <th class="pa-2">Month</th>
            <th class="pa-2" width="250px">Actual Grade(%)</th>
            <th class="pa-2" width="80px"> Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in monthly_key_performances" :class="index === editedIndex ? 'blue lighten-5' : ''">
            <td class="pa-2"> {{ index + 1 }} </td>

            <!-- START Show Data if row is not for edit (show by default) -->
            <td class="pa-2"> {{ item.year }} </td>
            <td class="pa-2"> {{ item.month }}</td>
            <template v-if="index !== editedIndex && item.status !== 'New'">
              <td class="pa-2"> {{ item.grade }} </td>
            </template>
            <!-- END Show Data if row is not for edit (show by default) -->

            <!-- START Show Fields if row is for edit -->
            <template v-if="index === editedIndex || item.status === 'New'">
              <td class="pa-2">
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
            <template v-if="index !== editedIndex && item.status !== 'New' ">
              <td class="pa-2">
                <v-icon
                  small
                  class="mr-2"
                  color="green"
                  @click="editItem(item)"
                  :disabled="actionMode === 'Add' ? true : false"
                >
                  mdi-pencil
                </v-icon>

                <v-icon
                  small
                  color="red"
                  @click="showConfirmAlert(item)"
                  :disabled="['Add', 'Edit'].includes(actionMode)"
                >
                  mdi-delete
                </v-icon>
              </td>
            </template>
            <!-- END  Show Edit(pencil icon) and Delete (trash icon) button if not Edit Mode (show by default) -->

            <!-- START  Show Save and Cancel button if Edit Mode -->
            <template v-if="index === editedIndex ? true : false || item.status === 'New' ">
              <td class="pa-2">
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
        <tfoot>
          <tr>
            <td colspan="9" class="text-right">
              <v-btn class="primary" x-small @click="newItem()" :disabled="['Add', 'Edit'].includes(actionMode)">add item</v-btn>
            </td>
          </tr>
        </tfoot>
      </template>
    </v-simple-table>
  </div>
</template>
<style scoped>
  .full-height {
    height: calc(85vh - 110px); /* Adjust 270px to suits your needs */
    overflow-y: auto;
    overflow-x: hidden;
  }
</style>
<script>

import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, requiredIf, email } from "vuelidate/lib/validators";

export default {

  props: {

  },

  mixins: [validationMixin],

  validations: {
    editedItem: { 
      grade: { required: requiredIf(function () {
            return this.actionMode;
          }),  
      },
    },
  },
  data() {
    return {
      editedIndex: -1,
      editedItem: {
        year: "",
        month: "",
        grade: "",
      },
      defaultField: {
        year: "",
        month: "",
        grade: "",
      },
      monthly_key_performances: [],
      actionMode: "",
      disabled: false,
    };
  },

  methods: {

    newItem() {
      this.resetData();
      this.actionMode = "Add";

      let hasNew = false;
      
      this.monthly_key_performances.forEach((value, index) => {
        if (value.status === "New") {
          hasNew = true;
        }
      });

      if (!hasNew) {
        this.monthly_key_performances.push({ status: "New" });
      }

    },

    saveItem(){
      console.log(this.editedItem);
      this.$v.editedItem.$touch();

      if(!this.$v.editedItem.$error)
      {
        if(this.actionMode === 'Add')
        {
          let index = this.monthly_key_performances.indexOf({ status: 'New' }); 
          this.monthly_key_performances.splice(index, 1);
          this.monthly_key_performances.push(this.editedItem);
        }
        else
        {
          this.monthly_key_performances[this.editedIndex] = this.editedItem;
        }

        this.resetData();
      }
      
    },

    cancelEvent(item) {
      this.editedIndex = this.monthly_key_performances.indexOf(item);
      if (this.actionMode === "Add") {
        this.monthly_key_performances.splice(this.editedIndex, 1);
      } 

      this.resetData();
    },

    editItem(item) {
      this.actionMode = "Edit";
      this.editedItem = Object.assign({}, item);
      this.editedIndex = this.monthly_key_performances.indexOf(item);
    },

    deleteItem(id) {
      const data = { roleid: roleid };
      this.loading = true;
      axios.post("/api/role/delete", data).then(
        (response) => {
          this.loading = false;
          this.showAlert(response.data.success);
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    resetData(){
      this.$v.editedItem.$reset();
      this.editedItem = Object.assign({}, this.defaultField);
      this.editedIndex = -1;
      this.actionMode = "";
    },

    showAlert(msg) {
      this.$swal({
        position: "center",
        icon: "success",
        title: ms,
        showConfirmButton: false,
        timer: 2500,
      });
    },

    showConfirmAlert(item) {
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
          // <-- if confirmed

          const id = item.id;
          const index = this.roles.indexOf(item);

          this.deleteItem(id);

          this.monthly_key_performances.splice(index, 1);
          
        }
      });
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
  computed: {
    
    gradeErrors(){
      const errors = [];
      if (!this.$v.editedItem.grade.$dirty) return errors;
      !this.$v.editedItem.grade.required && errors.push("Grade is required.");
      return errors;
    },
    
  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
  },
};
</script>
