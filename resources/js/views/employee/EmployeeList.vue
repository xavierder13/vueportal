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
          <div>
            <v-divider
              vertical
              class="ml-2 mr-2"
              v-if="userPermissions.employee_list_export"
            ></v-divider>
          </div>
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
          </v-data-table>

          <v-dialog v-model="dialog_import" max-width="500px" persistent>
            <v-card>
              <v-card-title class="mb-0 pb-0">
                <span class="headline">Import Projects</span>
              </v-card-title>
              <v-divider></v-divider>
              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col class="mt-0 mb-0 pt-0 pb-0">
                      <v-file-input
                        v-model="file"
                        show-size
                        label="File input"
                        prepend-icon="mdi-paperclip"
                        required
                        :error-messages="fileErrors"
                        @change="$v.file.$touch() + (fileIsEmpty = false)"
                        @blur="$v.file.$touch()"
                      >
                        <template v-slot:selection="{ text }">
                          <v-chip small label color="primary">
                            {{ text }}
                          </v-chip>
                        </template>
                      </v-file-input>
                    </v-col>
                  </v-row>
                  <v-row
                    class="fill-height"
                    align-content="center"
                    justify="center"
                    v-if="uploading"
                  >
                    <v-col class="subtitle-1 text-center" cols="12">
                      Uploading...
                    </v-col>
                    <v-col cols="6">
                      <v-progress-linear
                        color="primary"
                        indeterminate
                        rounded
                        height="6"
                      ></v-progress-linear>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                  color="#E0E0E0"
                  @click="dialog_import = false"
                  class="mb-4"
                >
                  Cancel
                </v-btn>
                <v-btn
                  color="primary"
                  class="mb-4 mr-4"
                  @click="uploadFile()"
                  :disabled="uploadDisabled"
                >
                  Upload
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <v-dialog v-model="dialog_error_list" max-width="1000px" persistent>
            <v-card>
              <v-card-title class="mb-0 pb-0">
                <span class="headline">Error List</span>
                <v-spacer></v-spacer>
                <v-icon @click="dialog_error_list = false"> mdi-close </v-icon>
              </v-card-title>
              <v-divider></v-divider>
              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col>
                      <v-simple-table dense>
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Error Message</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(item, index) in imported_file_errors">
                            <td>{{ index + 1 }}</td>
                            <td v-html="item"></td>
                          </tr>
                        </tbody>
                      </v-simple-table>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>
            </v-card>
          </v-dialog>
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
      // headers: [
      //   { text: "Branch", value: "branch.name" },
      //   { text: "Emp. Code", value: "employee_code" },
      //   { text: "Lastname", value: "last_name" },
      //   { text: "Firstname", value: "first_name" },
      //   { text: "Birthday", value: "dob" },
      //   { text: "Address", value: "address" },
      //   { text: "Contact #", value: "contact" },
      //   { text: "Email", value: "email" },
      //   { text: "Class", value: "class" },
      //   { text: "Rank", value: "rank" },
      //   { text: "Department", value: "department" },
      //   { text: "Cost Center Code", value: "cost_center_code" },
      //   { text: "Job Description", value: "job_description" },
      //   { text: "Date Employed", value: "date_employed" },
      //   { text: "Gender", value: "civil_status" },
      //   { text: "Civil Status", value: "civil_status" },
      //   { text: "Tax Status", value: "tax_status" },
      //   { text: "TIN #", value: "tin_no" },
      //   { text: "Tax Branch Code", value: "tax_branch_code" },
      //   { text: "Pag-IBIG #", value: "pagibig_no" },
      //   { text: "PhilHealth #", value: "philhealth_no" },
      //   { text: "SSS #", value: "sss_no" },
      //   { text: "Time Schedule", value: "time_schedule" },
      //   { text: "Restday", value: "restday" },
      //   { text: "Actions", value: "actions", sortable: false },
      // ],
      headers: [
        { text: 'Branch', value: 'name' },
        { text: 'Last Uploaded', value: 'last_upload' },
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
      search_branch: "",
      file: [],
      fileIsEmpty: false,
      fileIsInvalid: false,
      uploadDisabled: false,
      uploading: false,
      dialog_import: false,
      dialog_error_list: false,
      errors_array: [],
    };
  },

  methods: {
    getEmployee() {
      this.loading = true;
      axios.get("/api/employee/index").then(
        (response) => {
          this.employees = response.data.branches;
          this.branches = response.data.branches;
          this.branches.unshift({ id: 0, name: "ALL" });
          this.search_branch = this.user.branch_id;
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
      if (this.filteredEmployees.length) {
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

            axios.post("api/employee/clear_employee", data).then(
              (response) => {
                if (response.data.success) {
                  // send data to Sockot.IO Server
                  // this.$socket.emit("sendData", { action: "product-create" });

                  this.employees.forEach((value, index) => {
                    if (value.branch_id === this.search_branch) {
                      let i = this.employees.indexOf(value);
                      this.employees.splice(i, 1);
                    } else if (this.search_branch === 0) {
                      this.employees = [];
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

        axios
          .post(
            "api/employee/import_employee/" + this.search_branch,
            formData,
            {
              headers: {
                Authorization: "Bearer " + localStorage.getItem("access_token"),
                "Content-Type": "multipart/form-data",
              },
            }
          )
          .then(
            (response) => {
              this.errors_array = [];
              console.log(response.data);
              if (response.data.success) {
                // send data to Socket.IO Server
                // this.$socket.emit("sendData", { action: "import-project" });

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
              } else if (response.data.error_column) {
                this.errors_array = response.data.error_column;
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
        } else if (value.branch_id === 0 || !this.search_branch) {
          employees.push(value);
        }

      });

      return employees;
    },
    fileErrors() {
      const errors = [];
      if (!this.$v.file.$dirty) return errors;
      !this.$v.file.required && errors.push("File is required.");
      this.fileIsEmpty && errors.push("File is empty.");
      this.fileIsInvalid &&
        errors.push("File type must be 'xlsx', 'xls' or 'ods'.");
      return errors;
    },
    imported_file_errors() {
      return this.errors_array.sort();
    },
    ...mapState("auth", ["user"]),
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