<template>
  <div class="flex column">
    <div id="_wrapper" class="pa-5">
      <v-main>
        <v-breadcrumbs :items="items">
          <template v-slot:item="{ item }">
            <v-breadcrumbs-item :to="item.link" :disabled="item.disabled">
              {{ item.text.toUpperCase() }}
            </v-breadcrumbs-item>
          </template>
        </v-breadcrumbs>
        <div class="d-flex justify-content-end mb-3">
          <div>
            <v-btn
              color="primary"
              class="ml-4"
              small
              @click="importExcel()"
              v-if="userPermissions.employee_list_import"
            >
              <v-icon class="mr-1" small> mdi-import </v-icon>
              Import
            </v-btn>
          </div>
          <div><v-divider vertical class="ml-2 mr-2" v-if="userPermissions.employee_list_export"></v-divider></div>
          <div>
            <v-btn
              color="success"
              class="ml-4"
              small
              @click="exportData()"
              v-if="userPermissions.employee_list_export"
            >
              <v-icon class="mr-1" small> mdi-microsoft-excel </v-icon>
              Export
            </v-btn>
          </div>
          <div><v-divider vertical class="ml-2 mr-2"></v-divider></div>
          <div>
            <v-btn
              color="error"
              small
              @click="clearList()"
              v-if="userPermissions.employee_clear_list"
              ><v-icon class="mr-1" small> mdi-delete </v-icon>clear list</v-btn
            >
          </div>
        </div>
        <v-card>
          <v-card-title>
            Employee Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
            ></v-text-field>
            <v-spacer></v-spacer>
            <v-autocomplete
              v-model="search_branch"
              :items="branches"
              item-text="name"
              item-value="id"
              label="Branch"
              v-if="user.id === 1"
            >
            </v-autocomplete>
            <v-spacer></v-spacer>
          </v-card-title>
          <v-data-table
            :headers="headers"
            :items="filteredEmployees"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="userPermissions.employee_list"
          >

            <template v-slot:item.branch="{ item }">
              {{ item.branch.name }}
            </template>
            
          </v-data-table>
        </v-card>
      </v-main>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState } from "vuex";

export default {
  mixins: [validationMixin],

  validations: {
    file: { required },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Brand", value: "brand" },
        { text: "Model", value: "model" },
        { text: "Serial", value: "serial" },
        { text: "Branch", value: "branch" },
        { text: "Date Created", value: "date_created" },
        { text: "Actions", value: "actions", sortable: false },
      ],
      disabled: false,
      dialog: false,
      employees: [],
      branches: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Employee Lists",
          disabled: false,
        },
      ],
      loading: true,
      user: "",
      search_branch: "",
      file: [],
      fileIsEmpty: false,
      fileIsInvalid: false,
      uploadDisabled: false,
      uploading: false,
      dialog_import: false,
      dialog_error_list: false,
    };
  },

  methods: {
    getEmployee() {
      this.loading = true;
      axios.get("/api/employee/index").then(
        (response) => {
          this.employees = response.data.employees;
          this.branches = response.data.branches;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    showAlert() {
      this.$swal({
        position: "center",
        icon: "success",
        title: "Record has been saved",
        showConfirmButton: false,
        timer: 2500,
      });
    },

    clearList() {
      if (this.filteredemployees.length) {
        this.$swal({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#d33",
          cancelButtonColor: "#6c757d",
          confirmButtonText: "Clear List!",
        }).then((result) => {
          // <--

          if (result.value) {
            // <-- if confirmed

            let data = { branch_id: this.search_branch, clear_list: true };

            axios.post("api/employee/delete", data).then(
              (response) => {
                if (response.data.success) {
                  // send data to Sockot.IO Server
                  // this.$socket.emit("sendData", { action: "product-create" });

                  this.employees.forEach((value, index) => {
                    if (value.branch_id === this.search_branch) {
                      let i = this.employees.indexOf(value);
                      this.employees.splice(i, 1);
                    }
                  });

                  this.$swal({
                    position: "center",
                    icon: "success",
                    title: "Record has been cleared",
                    showConfirmButton: false,
                    timer: 2500,
                  });
                } else {
                  this.$swal({
                    position: "center",
                    icon: "warning",
                    title: "No record found",
                    showConfirmButton: false,
                    timer: 2500,
                  });
                }
              },
              (error) => {
                this.isUnauthorized(error);
              }
            );
          }
        });
      } else {
        this.$swal({
          position: "center",
          icon: "warning",
          title: "No record found",
          showConfirmButton: false,
          timer: 2500,
        });
      }
    },

    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedItem.branch_id = this.user.branch_id;
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },

    importExcel() {
      this.dialog_import = true;
      this.file = [];
      this.$v.$reset();
    },

    uploadFile() {
      this.$v.$touch();
      this.fileIsEmpty = false;
      this.fileIsInvalid = false;

      if (!this.$v.file.$error) {
        this.uploadDisabled = true;
        this.uploading = true;
        let formData = new FormData();

        formData.append("file", this.file);

        axios.post("api/employee/import_employee", formData, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token'),
            "Content-Type": "multipart/form-data",
          },
        }).then(
          (response) => {
            this.errors_array = [];

            if (response.data.success) {
              // send data to Socket.IO Server
              this.$socket.emit("sendData", { action: "import-project" });

              this.$swal({
                position: "center",
                icon: "success",
                title: "Record has been imported",
                showConfirmButton: false,
                timer: 2500,
              });
              this.$v.$reset();
              this.dialog_import = false;
              this.file = [];
              this.fileIsEmpty = false;
            } else if (response.data.error_column_name) {
              this.errors_array = response.data.error_column_name;
              this.dialog_error_list = true;
            } else if (response.data.error_row_data) {
              let error_keys = Object.keys(response.data.error_row_data);
              let errors = response.data.error_row_data;
              let field_values = response.data.field_values;
              let row = "";
              let col = "";

              error_keys.forEach((value, index) => {
                row = value.split(".")[0];
                col = value.split(".")[1];
                errors[value].forEach((val, i) => {
                  this.errors_array.push(
                    "Error on Index: <label class='text-info'>" +
                      row +
                      "</label>; Column: <label class='text-primary'>" +
                      col +
                      "</label>; Msg: <label class='text-danger'>" +
                      val +
                      "</label>; Value: <label class='text-success'>" +
                      field_values[row][col] +
                      "</label>"
                  );
                });
              });

              this.dialog_error_list = true;
            } else if (response.data.error_empty) {
              this.fileIsEmpty = true;
            } else {
              this.fileIsInvalid = true;
            }

            this.uploadDisabled = false;
            this.uploading = false;
          },
          (error) => {
            this.isUnauthorized(error);
            this.uploadDisabled = false;
          }
        );
      }
    },
    exportData() {
      if (this.filteredEmployees.length) {
        window.open(
          location.origin + "/api/employee/export/" + this.user.branch_id,
          "_blank"
        );
      } else {
        this.$swal({
          position: "center",
          icon: "warning",
          title: "No record found",
          showConfirmButton: false,
          timer: 2500,
        });
      }
    },

    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;

        if (
          action == "employee-create" ||
          action == "employee-edit" ||
          action == "employee-delete" ||
          action == "employee-import"
        ) {
          this.getEmployee();
        }
      };
    },
    
  },
  computed: {
    
    filteredEmployees() {
      let employees = [];

      this.employees.forEach((value) => {
        if (value.branch_id === this.search_branch) {
          employees.push(value);
        }
      });

      return employees;
    },
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
  },
  
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getEmployee();
    // this.websocket();
  },
};
</script>