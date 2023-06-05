<template>
  <div>
    <v-dialog v-model="dialog_import" max-width="500px" persistent>
      <v-card>
        <v-card-title class="pa-4">
          <span class="headline">Import Data</span>
        </v-card-title>
        <v-divider class="mt-0"></v-divider>
        <v-card-text>
          <v-container>
            <v-row v-if="userHasPermission">
              <v-col class="my-0 py-0">
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
            <v-row v-if="action === 'sync'"> 
              <v-col class="my-0 py-0">
                <v-autocomplete
                  v-model="database"
                  :items="databases"
                  item-text="database"
                  item-value="database"
                  label="SAP Database"
                  required
                  :error-messages="databaseErrors"
                  @input="$v.database.$touch()"
                  @blur="$v.database.$touch()"
                >
                </v-autocomplete>
              </v-col>
            </v-row>
            <v-row v-if="action === 'import'">
              <v-col class="my-0 py-0">
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
                {{ loadingLabel }}
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
        <v-divider class="mb-3 mt-0"></v-divider>
        <v-card-actions class="pa-0">
          <v-spacer></v-spacer>
          <v-btn
            color="#E0E0E0"
            @click="closeDialog()"
            class="mb-3"
          >
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            class="mb-3 mr-4"
            @click="submit()"
            :disabled="uploadDisabled"
          >
            {{ btnLabel }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialog_error_list" max-width="1000px" persistent>
      <v-card>
        <v-card-title class="pa-4">
          <span class="headline">Error List</span>
          <v-spacer></v-spacer>
          <v-btn @click="dialog_error_list = false" icon>
            <v-icon> mdi-close </v-icon>
          </v-btn>
        </v-card-title>
        <v-divider class="mt-0"></v-divider>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col>
                <v-simple-table dense>
                  <thead>
                    <tr>
                      <th width="10px">#</th>
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
  </div>
</template>

<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, requiredIf } from "vuelidate/lib/validators";
import { mapState, mapGetters } from "vuex";
export default {
  
  name: "ImportDialog",
  props: {
    api_route: String,
    dialog_import: Boolean,
    branches: Array,
    databases: Array,
    action: String,
    inventory_group: String,
  },

  mixins: [validationMixin],
  validations: {
    branch_id: { required },
    file: { 
      required: requiredIf(function () {
        return this.action === 'import';
      }) 
    },
    database: { 
      required: requiredIf(function () {
        return this.action === 'sync';
      }) 
    },
  },
  data () {
    return {
      branch_id: "",
      docdate: new Date().toISOString().substr(0, 10),
      file: [],
      database: "",
      loading: true,
      uploadDisabled: false,
      uploading: false,
      dialog_error_list: false,
      errors_array: [],
      input_docdate: false,
    }
  },
  methods: {
    async submit() {
      await this.$v.$touch();

      if (!this.$v.$error) {
        this.uploadDisabled = true;
        this.uploading = true;
        if(this.action === 'import')
        {
          await this.uploadFile(); //upload file
        }
        else {
          await this.syncInventoryRecon(); //sync data from SAP
        }
      }
    
    },
    syncInventoryRecon() {
      this.syncIsClicked = true;

      const data = { 
        branch_id: this.branch_id, 
        database: this.database, 
        inventory_group: this.inventory_group, 
      };

      axios.post(this.api_route, data).then(
          (response) => {
            let data = response.data;
            
            this.uploadDisabled = false;
            this.uploading = false;
            this.$emit('closeImportDialog');

            if(data.error)
            {
              this.showErrorAlert('Error', data.error);
            }

            if (data.success) {
              // send data to Socket.IO Server
              // this.$socket.emit("sendData", { action: "import-project" });

              this.$emit('getInventory');
              
              this.showAlert(data.success, 'success');

              this.$v.$reset();
              
            } else if (data.empty) {

              this.showAlert(data.empty, 'warning');
            } 

          },
          (error) => {
            console.log(error);
            this.uploadDisabled = false;
            this.showErrorAlert(error, error.response.data.message);
            this.isUnauthorized(error);
           
          }
        );
    },

    uploadFile() {
      
      this.fileIsEmpty = false;
      this.fileIsInvalid = false;

      let formData = new FormData();

      formData.append("file", this.file);
      formData.append("inventory_group", this.inventory_group);
      formData.append("branch_id", this.branch_id);
    
      axios
        .post(this.api_route, formData, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem("access_token"),
            "Content-Type": "multipart/form-data",
          },
        })
        .then(
          (response) => {
            
            this.errors_array = [];
            let data = response.data
            this.$emit('closeImportDialog');
       
            if (data.success) {
              // send data to Socket.IO Server
              // this.$socket.emit("sendData", { action: "import-project" });
              this.$emit('getInventory');
              
              this.showAlert(data.success, 'success');

              this.$v.$reset();
              this.file = [];
              this.fileIsEmpty = false;
              this.branch_id = "";
            } else if (data.error_column) {
              this.errors_array = data.error_column;
              this.dialog_error_list = true;
            } else if (data.error_row_data) {
              let error_keys = Object.keys(data.error_row_data);
              let errors = data.error_row_data;
              let field_values = data.field_values;
              let row = "";
              let col = "";

              error_keys.forEach((value, index) => {
                row = value.split(".")[0];
                col = value.split(".")[1];
                errors[value].forEach((val, i) => {
                  this.errors_array.push(
                    "Error on row: <label class='text-info'>" +
                      (parseInt(row) + 1) +
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
            } else if (data.error_empty) {
              this.fileIsEmpty = true;
            } 
            else if (data.duplicate_serials)
            {
              let error_keys = Object.keys(data.duplicate_serials);
              let errors = data.duplicate_serials;
              
              error_keys.forEach(val => {
                this.errors_array.push(
                  "Duplicate Serial # on row: <label class='text-info'>" +
                    parseInt(val) +
                    "</label>; Serial: <label class='text-danger'>" +
                    errors[val] +
                    "</label>"
                );
              });
              
            }
            else if(data.error)
            {
              this.showErrorAlert("ERROR!", data.error);
            }
            else {
              this.fileIsInvalid = true;
            }

            this.uploadDisabled = false;
            this.uploading = false;
          },
          (error) => {
            console.log(error);
            this.uploadDisabled = false;
          }
        );
      
    },
    closeDialog() {
      this.$emit('closeImportDialog');
      this.file = [];
      this.branch_id = "";
      this.database = "";
      this.fileIsEmpty = false;
      this.uploadDisabled = false;
      this.uploading = false;
      this.fileIsInvalid = false;
      this.$v.$reset();
    }, 

    showAlert(title, icon) {
      this.$swal({
        position: "center",
        icon: icon,
        title: title,
        showConfirmButton: false,
        timer: 2500,
      });
    },
    showErrorAlert(title, text) {
      this.$swal({
        position: "center",
        icon: "error",
        title: title,
        text: text,
        showConfirmButton: false,
        timer: 10000,
      });
    },
    reset() {

    }
  },
  computed: {
    fileErrors() {
      const errors = [];
      if (!this.$v.file.$dirty) return errors;
      !this.$v.file.required && errors.push("File is required.");
      this.fileIsEmpty && errors.push("File is empty.");
      this.fileIsInvalid &&
        errors.push("File type must be 'xlsx', 'xls' or 'ods'.");
      return errors;
    },
    branchErrors() {
      const errors = [];
      if (!this.$v.branch_id.$dirty) return errors;
      !this.$v.branch_id.required && errors.push("Branch is required.");
      return errors;
    },
    databaseErrors() {
      const errors = [];
      if (!this.$v.database.$dirty) return errors;
      !this.$v.database.required && errors.push("SAP Database is required.");
      return errors;
    },
    imported_file_errors() {
      return this.errors_array.sort();
    },
    btnLabel(){
      return this.action === 'import' ? 'Upload' : 'Sync';
    },
    loadingLabel(){
      return this.action === 'import' ? 'Uploading...' : 'Syncing...';
    },
    formtitle(){
      return this.action === 'import' ? 'Import Excel Data' : 'Sync Data From SAP';
    },
    userHasPermission(){
      return this.hasAnyRole('Administrator', 'Audit Admin', 'Inventory Admin');
    },
    ...mapState("auth", ["user"]),
    ...mapGetters("userRolesPermissions", ["hasAnyRole", "hasPermission"]),
  },
  
}

</script>
