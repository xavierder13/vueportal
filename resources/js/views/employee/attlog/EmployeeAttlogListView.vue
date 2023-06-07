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
        <MenuActions
          :canImport="hasPermission('employee-attlog-import')"
          :canDownloadFile="hasPermission('employee-attlog-export')"
          :canExport="hasPermission('employee-attlog-export')"
          :canClearList="hasPermission('employee-attlog-clear-list')"
          @import="importExcel"
          @downloadFile="downloadFile"
          @export="exportData"
          @clearList="clearList"
        />
        <!-- <div 
          class="d-flex justify-content-end mb-3"
          v-if="hasAnyPermission('employee-attlog-import', 'employee-attlog-export', 'employee-attlog-clear-list')"
        >
          <div>
            <v-menu offset-y>
              <template v-slot:activator="{ on, attrs }">
                <v-btn small v-bind="attrs" v-on="on" color="primary">
                  Actions
                  <v-icon small> mdi-menu-down </v-icon>
                </v-btn>
              </template>
              <v-list class="pa-1">
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="hasPermission('employee-attlog-import')"
                >
                  <v-list-item-title>
                    <v-btn
                      color="primary"
                      class="mx-1"
                      width="100px"
                      x-small
                      @click="importExcel()"
                    >
                      <v-icon class="mr-1" small> mdi-import </v-icon>
                      Import
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="hasPermission('employee-attlog-export')"
                >
                  <v-list-item-title>
                    <v-btn
                      color="success"
                      class="mx-1"
                      width="100px"
                      x-small
                      @click="exportData()"
                    >
                      <v-icon class="mr-1" x-small> mdi-microsoft-excel </v-icon>
                      Export
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="hasPermission('employee-attlog-export')"
                >
                  <v-list-item-title>
                    <v-btn
                      color="info"
                      class="mx-1"
                      width="100px"
                      x-small
                      @click="downloadFile()"
                    >
                      <v-icon class="mr-1" x-small> mdi-download </v-icon>
                      File
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="hasPermission('employee-attlog-clear-list')"
                >
                  <v-list-item-title>
                    <v-btn
                      color="error"
                      class="mx-1"
                      width="100px"
                      x-small
                      @click="clearList()"
                      ><v-icon class="mr-1" x-small> mdi-delete </v-icon>clear
                      list</v-btn
                    >
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </div>
        </div> -->
        <v-card>
          <v-card-title>
            Employee Attlog Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
            ></v-text-field>
            <v-spacer></v-spacer>

            <v-btn
              color="primary"
              fab
              dark
              class="mb-2"
              @click="clear() + (dialog = true)"
              v-if="hasPermission('employee-attlog-create')"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
          </v-card-title>
          <!-- <DataTable
            :headers="headers"
            :items="employee_attlogs"
            :search="search"
            :loading="loading"
            :file_upload_log="file_upload_log" 
            :canViewList="hasPermission('employee-attlog-list')"
            :canEdit="hasPermission('employee-attlog-edit')"
            :canDelete="hasPermission('employee-attlog-delete')"
            @edit="editEmployeeAttlog"
            @confirmDelete="showConfirmAlert"
          /> -->
          <v-data-table
            :headers="headers"
            :items="employee_attlogs"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="hasPermission('employee-attlog-list')"
          > 
            <template v-slot:top v-if="file_upload_log">
              <v-toolbar
                flat
              >
                <h6 class="my-0 font-weight-bold">Document Date:</h6>  <v-chip color="secondary" class="ml-2">{{ file_upload_log.docdate }}</v-chip>
                <h6 class="my-0 font-weight-bold ml-8">Uploaded Date:</h6>  <v-chip color="secondary" class="ml-2">{{ file_upload_log.date_uploaded }}</v-chip>
              </v-toolbar>
            </template>
            <template v-slot:item.branch="{ item }">
              {{ item.branch.name }}
            </template>
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editEmployeeAttlog(item)"
                v-if="hasPermission('employee-attlog-edit')"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="hasPermission('employee-attlog-delete')"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-data-table>
          <ImportDialog 
            :api_route="api_route" 
            :dialog_import="dialog_import"
            @getData="getEmployeeAttlog"
            @closeImportDialog="closeImportDialog"
          />
        </v-card>
      </v-main>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState, mapGetters } from "vuex";
import ImportDialog from "../components/ImportDialog.vue";
import MenuActions from '../../components/MenuActions.vue';

export default {
  name: "EmployeeAttlogListView",
  components: {
    ImportDialog,
    MenuActions
  },
  props: {

  },
  mixins: [validationMixin],

  validations: {
    file: { required },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "branch" },
        { text: "User ID", value: "user_id" },
        { text: "Date and Time", value: "date_time" },
        { text: "Device ID", value: "device_id" },
        { text: "Status", value: "status" },
        { text: "Verifying Type", value: "verifying_type" },
        { text: "Work Code", value: "work_code" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      disabled: false,
      dialog: false,
      employee_attlogs: [],
      branches: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Employee Attlog Lists",
          disabled: false,
          link: "/employee/attlog/list",
        },
        {
          text: "View",
          disabled: true,
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
      fileError: "",
      branch_id: "",
      editedIndex: -1,
      editedItem: {	
      },
      defaultItem: {
       
      },
      file_upload_log: "",
      dialog_import: false,
      api_route: "",
    };
  },

  watch: {
    userIsLoaded: {
      handler() {
        if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
          this.getEmployeeAttlog(this.$route.params.file_upload_log_id);
        }
      },
    },
    userRolesPermissionsIsLoaded: {
      handler() {
        if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
          this.getEmployeeAttlog(this.$route.params.file_upload_log_id);
        }
      },
    },
  },

  methods: {
    getEmployeeAttlog(file_upload_log_id) {

      this.loading = true;
      let data = { file_upload_log_id: file_upload_log_id }
      axios.post("/api/employee_attlog/list/view", data).then(
        (response) => {
          // if user has no permission to view overall list
          
          if (
            !this.hasPermission('employee-attlog-list-all') &&
            this.user.branch_id != this.branch_id
          ) {
            this.$router.push({ name: "unauthorize" });
          }
            
          this.file_upload_log = response.data.file_upload_log;
          this.employee_attlogs = response.data.employee_attlogs;
          this.loading = false;

        },
        (error) => {
          this.isUnauthorized(error);
          console.log(error);
        }
      );
    },

    deleteEmployeeAttlog(employee_attlog_id) {
      const data = { employee_attlog_id: employee_attlog_id };

      axios.post("/api/employee_attlog/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "employee-delete" });
          }
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

          const employee_attlog_id = item.id;
          const index = this.employee_attlogs.indexOf(item);

          //Call delete User function
          this.deleteEmployeeAttlog(employee_attlog_id);

          //Remove item from array employee_attlogs
          this.employee_attlogs.splice(index, 1);

          this.$swal({
            position: "center",
            icon: "success",
            title: "Record has been deleted",
            showConfirmButton: false,
            timer: 2500,
          });
        }
      });
    },

    close() {
      this.dialog = false;
      this.loading = false;
      this.clear();
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    closeImportDialog() {
      this.dialog_import = false;
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
      if (this.employee_attlogs.length) {
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

            let data = { 
              branch_id: this.branch_id, 
              file_upload_log_id: this.file_upload_log_id, 
              clear_list: true 
            };

            axios.post("api/employee_attlog/delete", data).then(
              (response) => {
                if (response.data.success) {
                  // send data to Sockot.IO Server
                  // this.$socket.emit("sendData", { action: "product-create" });
              
                  this.employee_attlogs = [];
                  this.file_upload_log = null;

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
                console.log(response.data);
              },
              (error) => {
                this.isUnauthorized(error);
                console.log(error);
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
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },

    importExcel() {
      this.dialog_import = true;
    },

    exportData() {
      if (this.employee_attlogs.length) {
        window.open(
          location.origin + "/api/employee_attlog/export_attlog/" + this.file_upload_log_id,
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
    downloadFile() {
      window.open(location.origin + "/api/employee_attlog/file/download?file_upload_log_id=" + this.file_upload_log_id, "_blank");
    },
    formatDate(date) {
      if (!date) return null;
      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },
    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;

        if (
          action == "employee-attlog-create" ||
          action == "employee-attlog-edit" ||
          action == "employee-attlog-delete" ||
          action == "employee-attlog-import" ||
          action == "employee-attlog-clear-list"
        ) {
          this.getEmployeeAttlog();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Employee Attlog Information" : "Edit Employee Information";
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
    ...mapState("auth", ["user", "userIsLoaded"]),
    ...mapState("userRolesPermissions", ["userRolesPermissionsIsLoaded"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),
  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.branch_id = this.$route.params.branch_id;
    this.file_upload_log_id = this.$route.params.file_upload_log_id;
    this.api_route = 'api/employee_attlog/import_attlog/' + this.branch_id; //set api route for uploading excel
    
    if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
      this.getEmployeeAttlog(this.file_upload_log_id);
    }

    // this.websocket();
  },
};
</script>