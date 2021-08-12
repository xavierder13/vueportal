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
            <v-btn color="primary" class="ml-4" small @click="importExcel()">
              <v-icon class="mr-1" small> mdi-import </v-icon>
              Import
            </v-btn>
          </div>
        </div>
        <v-card>
          <v-card-title>
            Inventory Reconciliations
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
            ></v-text-field>
            <v-spacer></v-spacer>
            <v-autocomplete
              v-model="inventory_group"
              :items="inventory_groups"
              item-text="name"
              item-value="name"
              label="Inventory Group"
              v-if="user.id === 1"
            >
            </v-autocomplete>
            <v-spacer></v-spacer>
          </v-card-title>

          <v-data-table
            :headers="headers"
            :items="filteredInventory"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="userPermissions.inventory_recon_list"
          >
            <template v-slot:item.status="{ item }">
              <v-chip
                :color="
                  item.status == 'unreconciled' ? 'red white--text' : 'success'
                "
              >
                {{ item.status }}
              </v-chip>
            </template>
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="info"
                @click="viewReconciliation(item)"
              >
                mdi-eye
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="userPermissions.inventory_recon_delete"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-data-table>

          <v-dialog v-model="dialog_import" max-width="500px" persistent>
            <v-card>
              <v-card-title class="mb-0 pb-0">
                <span class="headline">Import Data from SAP</span>
              </v-card-title>
              <v-divider></v-divider>
              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col class="mt-0 mb-0 pt-0 pb-0">
                      <v-autocomplete
                        v-model="branch_id"
                        :items="branches"
                        item-text="name"
                        item-value="id"
                        label="Branch"
                        required
                        :error-messages="branchErrors"
                        @input="$v.branch_id.$touch()"
                        @blur="$v.branch_id.$touch()"
                      >
                      </v-autocomplete>
                    </v-col>
                  </v-row>
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
                  @click="clear() + (dialog_import = false)"
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
    branch_id: { required },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "branch.name" },
        { text: "Created By", value: "user.name" },
        { text: "Status", value: "status" },
        { text: "Date Created", value: "date_created" },
        { text: "Date Reconciled", value: "date_reconciled" },
        { text: "Actions", value: "actions", sortable: false },
      ],
      disabled: false,
      dialog: false,
      inventory_reconciliations: [],
      branches: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Inventory Reconciliations",
          disabled: true,
          link: "/employee/list",
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
      branch_id: "",
      inventory_groups: [{ name: "Admin-Branch" }, { name: "Audit-Branch" }],
      inventory_group: "Admin-Branch",
    };
  },

  methods: {
    getInventory() {
      this.loading = true;
      axios.get("/api/inventory_reconciliation/index").then(
        (response) => {
          this.inventory_reconciliations =
            response.data.inventory_reconciliations;
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

    deleteInventory(inventory_recon_id) {
      const data = { inventory_recon_id: inventory_recon_id };
      this.loading = true;
      axios.post("/api/inventory_reconciliation/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "inventory-delete" });
          }
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
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

          const inventory_recon_id = item.id;
          const index = this.inventories.indexOf(item);

          //Call delete Product function
          this.deleteInventory(inventory_recon_id);

          //Remove item from array inventories
          this.inventories.splice(index, 1);

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

    viewReconciliation(item) {
      this.$router.push({
        name: "inventory.reconciliation.view",
        params: { inventory_recon_id: item.id },
      });
    },

    clear() {
      this.$v.$reset();
      this.branch_id = "";
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },

    importExcel() {
      this.dialog_import = true;
      this.clear();
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
        formData.append("inventory_group", this.inventory_group);
        formData.append("branch_id", this.branch_id);

        axios
          .post("api/inventory_reconciliation/import", formData, {
            headers: {
              Authorization: "Bearer " + localStorage.getItem("access_token"),
              "Content-Type": "multipart/form-data",
            },
          })
          .then(
            (response) => {
              this.errors_array = [];

              if (response.data.success) {
                // send data to Socket.IO Server
                // this.$socket.emit("sendData", { action: "import-project" });
                this.getInventory();
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
          this.getInventory();
        }
      };
    },
  },
  computed: {
    filteredInventory() {
      let inventory = [];

      if (this.user.id !== 1) {
        // if user has role Audit Admin
        if (this.userRoles.audit_admin) {
          this.inventory_group = "Audit-Branch";
        }
        // if user has role Inventory Admin
        else if (this.userRoles.inventory_admin) {
          this.inventory_group = "Admin-Branch";
        }
      }

      this.inventory_reconciliations.forEach((value, index) => {
        if (value.inventory_group === this.inventory_group) {
          inventory.push(value);
        }
      });

      return inventory;
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
    branchErrors() {
      const errors = [];
      if (!this.$v.branch_id.$dirty) return errors;
      !this.$v.branch_id.required && errors.push("Branch is required.");
      return errors;
    },
    ...mapState("auth", ["user"]),
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");

    this.getInventory();
    // this.websocket();
  },
};
</script>