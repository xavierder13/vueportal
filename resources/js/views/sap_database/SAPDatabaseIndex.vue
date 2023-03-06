<template>
  <div class="flex column">
    <div id="_wrapper" class="pa-5">
      <v-overlay :absolute="absolute" :value="overlay">
        <v-progress-circular
          :size="70"
          :width="7"
          color="primary"
          indeterminate
        ></v-progress-circular>
      </v-overlay>
      <v-main>
        <v-breadcrumbs :items="items">
          <template v-slot:item="{ item }">
            <v-breadcrumbs-item :to="item.link" :disabled="item.disabled">
              {{ item.text.toUpperCase() }}
            </v-breadcrumbs-item>
          </template>
        </v-breadcrumbs>
        <v-card>
          <v-card-title>
            SAP Database Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="userPermissions.sap_database_list"
            ></v-text-field>
            <template>
              <v-toolbar flat>
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  fab
                  dark
                  class="mb-2"
                  @click="clear() + (dialog = true)"
                  v-if="userPermissions.sap_database_create"
                >
                  <v-icon>mdi-plus</v-icon>
                </v-btn>

                <v-dialog v-model="dialog" max-width="500px" persistent>
                  <v-card>
                    <v-card-title class="mb-0 pb-0">
                      <span class="headline">{{ formTitle }}</span>
                    </v-card-title>
                    <v-divider></v-divider>
                    <v-card-text>
                      <v-container>
                        <v-row>
                          <v-col class="my-0 py-0">
                            <v-text-field
                              name="server"
                              v-model="editedItem.server"
                              :error-messages="serverErrors"
                              label="Server"
                              @input="$v.editedItem.server.$touch()"
                              @blur="$v.editedItem.server.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="my-0 py-0">
                            <v-text-field
                              name="database"
                              v-model="editedItem.database"
                              :error-messages="databaseErrors"
                              label="Database"
                              @input="$v.editedItem.database.$touch()"
                              @blur="$v.editedItem.database.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="my-0 py-0">
                            <v-text-field
                              name="username"
                              v-model="editedItem.username"
                              :error-messages="usernameErrors"
                              label="Username"
                              @input="$v.editedItem.username.$touch()"
                              @blur="$v.editedItem.username.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="my-0 py-0">
                            <v-text-field
                              name="password"
                              v-model="password"
                              :error-messages="passwordErrors"
                              label="Password"
                              required
                              @input="$v.password.$touch()"
                              @blur="$v.password.$touch() + dummyPassword"
                              @keyup="passwordChange()"
                              @focus="onFocus()"
                              type="password"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="my-0 py-0">
                            <v-text-field
                              name="confirm_password"
                              v-model="confirm_password"
                              :error-messages="confirm_passwordErrors"
                              label="Confirm Password"
                              required
                              @input="$v.confirm_password.$touch()"
                              @blur="$v.confirm_password.$touch() + dummyPassword"
                              @keyup="passwordChange()"
                              @focus="onFocus()"
                              type="password"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                      </v-container>
                    </v-card-text>
                    <v-divider class="mb-3 mt-0"></v-divider>
                    <v-card-actions class="pa-0">
                      <v-spacer></v-spacer>
                      <v-btn color="#E0E0E0" @click="close" class="mb-3">
                        Cancel
                      </v-btn>
                      <v-btn
                        color="primary"
                        @click="save"
                        :disabled="disabled"
                        class="mb-3 mr-4"
                      >
                        Save
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
              </v-toolbar>
            </template>
          </v-card-title>
          <v-data-table
            :headers="headers"
            :items="sap_databases"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="userPermissions.sap_database_list"
          >
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editSAPDatabase(item)"
                v-if="userPermissions.sap_database_edit"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="userPermissions.sap_database_delete"
              >
                mdi-delete
              </v-icon>
            </template>
            <template v-slot:item.password="{ item }">
              ********
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
import {
  required,
  maxLength,
  email,
  minLength,
  sameAs,
} from "vuelidate/lib/validators";
import { mapState } from "vuex";

export default {
  mixins: [validationMixin],

  validations: {
    editedItem: {
      server: { required },
      database: { required },
      username: { required },
    },
    password: { required, minLength: minLength(8) },
    confirm_password: { required, sameAsPassword: sameAs("password") },
  },
  data() {
    return {
      absolute: true,
      overlay: false,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "SAP Database Lists",
          disabled: true,
        },
      ],
      search: "",
      search_roles_permissions: "",
      headers: [
        { text: "Server", value: "server" },
        { text: "Database", value: "database" },
        { text: "Username", value: "username" },
        { text: "Password", value: "password" },
        { text: "Actions", value: "actions", sortable: false, width: "80px"},
      ],
      disabled: false,
      dialog: false,
      sap_databases: [],
      editedIndex: -1,
      editedItem: {
        server: "",
        database: "",
        username: "",
      },
      defaultItem: {
        server: "",
        database: "",
        username: "",
      },
      password: "",
      confirm_password: "",
      loading: true,
      passwordHasChanged: false,
    };
  },

  methods: {
    getSAPDatabase() {
      this.loading = true;
      axios.get("/api/sap_database/index").then(
        (response) => {
          let data = response.data;
          this.sap_databases = data.sap_databases;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    editSAPDatabase(item) {
      this.editedIndex = this.sap_databases.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
      this.emailReadonly = true;
      this.password = "password";
      this.confirm_password = "password";
      this.switch1 = true;
      if(this.editedItem.active === 'N')
      {
        this.switch1 = false;
      }
    },

    deleteUser(sap_database_id) {
      const data = { sap_database_id: sap_database_id };

      axios.post("/api/sap_database/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "sap-database-delete" });
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

          const sap_database_id = item.id;
          const index = this.sap_databases.indexOf(item);

          //Call delete User function
          this.deleteUser(sap_database_id);

          //Remove item from array services
          this.sap_databases.splice(index, 1);

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
      this.clear();
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    save() {
      this.$v.$touch();

      if (!this.$v.$error) {
        this.disabled = true;
        this.overlay = true;

        if (this.editedIndex > -1) {
          if (this.passwordHasChanged) {
            this.editedItem.password = this.password;
            this.editedItem.confirm_password = this.confirm_password;
          }

          const data = this.editedItem;
          const sap_database_id = this.editedItem.id;

          axios.post("/api/sap_database/update/" + sap_database_id, data).then(
            (response) => {
              console.log(response.data);
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "sap-database-edit" });

                Object.assign(this.sap_databases[this.editedIndex], response.data.sap_database);
                this.showAlert();
                this.close();

              } 
              
              this.overlay = false;
              this.disabled = false;
            },
            (error) => {
              this.isUnauthorized(error);

              this.overlay = false;
              this.disabled = false;
            }
          );
        } else {
          
          this.editedItem.password = this.password;
          this.editedItem.confirm_password = this.confirm_password;

          const data = this.editedItem;

          axios.post("/api/sap_database/store", data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "sap-database-create" });

                this.showAlert();
                this.close();

                //push recently added data from database
                this.sap_databases.push(response.data.sap_database);
              } 

              this.overlay = false;
              this.disabled = false;
            },
            (error) => {
              this.isUnauthorized(error);

              this.overlay = false;
              this.disabled = false;
            }
          );
        }
      }
    },
    clear() {
      this.$v.$reset();
      this.emailReadonly = false;
      this.password = "";
      this.confirm_password = "";
      this.passwordHasChanged = false;
    },
    onFocus() {
      if (this.editedIndex > -1 && this.editedItem.id != 1) {
        if (!this.passwordHasChanged) {
          this.password = "";
          this.confirm_password = "";
        }
      }
    },
    passwordChange() {
      if (this.password || this.confirm_password) {
        this.passwordHasChanged = true;
      } else {
        this.passwordHasChanged = false;
      }
    },
    viewRolesBreakdown(roles) {
      this.dialogPermission = true;
      this.roles_permissions = roles;
    },
    closeRolesBreakdown() {
      this.dialogPermission = false;
      this.search_roles_permissions = "";
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },

    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;

        if (
          action == "sap-database-create" ||
          action == "sap-database-edit" ||
          action == "sap-database-delete" ||
          action == "login"
        ) {
          this.getSAPDatabase();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1
        ? "New SAP Databse"
        : "Edit SAP Databse";
    },
    serverErrors() {
      const errors = [];
      if (!this.$v.editedItem.server.$dirty) return errors;
      !this.$v.editedItem.server.required && errors.push("Server is required.");
      return errors;
    },
    databaseErrors() {
      const errors = [];
      if (!this.$v.editedItem.database.$dirty) return errors;
      !this.$v.editedItem.database.required && errors.push("Database is required.");
      return errors;
    },
    usernameErrors() {
      const errors = [];
      if (!this.$v.editedItem.username.$dirty) return errors;
      !this.$v.editedItem.username.required && errors.push("Username is required.");
      return errors;
    },
    passwordErrors() {
      const errors = [];
      if (!this.$v.password.$dirty) return errors;
      !this.$v.password.required && errors.push("Password is required.");
      !this.$v.password.minLength &&
        errors.push("Password must be atleast 8 characters.");
      return errors;
    },

    confirm_passwordErrors() {
      const errors = [];
      if (!this.$v.confirm_password.$dirty) return errors;
      !this.$v.confirm_password.required &&
        errors.push("Password Confirmation is required.");
      !this.$v.confirm_password.sameAsPassword &&
        errors.push("Passwords did not match");
      return errors;
    },

    dummyPassword() {
      if (this.editedIndex > -1) {
        if (!this.password && !this.confirm_password) {
          this.password = "password";
          this.confirm_password = "password";
        }
      }
    },
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getSAPDatabase();
    // this.websocket();
  },
};
</script>