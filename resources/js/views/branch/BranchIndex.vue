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
        <v-card>
          <v-card-title>
            Branch Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="userPermissions.branch_list"
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
                  v-if="userPermissions.branch_create"
                >
                  <v-icon>mdi-plus</v-icon>
                </v-btn>
                <v-dialog v-model="dialog" max-width="500px" persistent>
                  <v-card>
                    <v-card-title>
                      <span class="headline">{{ formTitle }}</span>
                    </v-card-title>
                    <v-divider></v-divider>
                    <v-card-text>
                      <v-container>
                        <v-row>
                          <v-col class="mt-0 mb-0 pt-0 pb-0">
                            <v-text-field
                              name="branch_code"
                              v-model="editedBranch.code"
                              label="Branch Code"
                              required
                              :error-messages="branchCodeErrors"
                              @input="$v.editedBranch.code.$touch()"
                              @blur="$v.editedBranch.code.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="mt-0 mb-0 pt-0 pb-0">
                            <v-text-field
                              name="branch"
                              v-model="editedBranch.name"
                              label="Branch"
                              required
                              :error-messages="branchErrors"
                              @input="$v.editedBranch.name.$touch()"
                              @blur="$v.editedBranch.name.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                      </v-container>
                    </v-card-text>

                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="#E0E0E0" @click="close" class="mb-4">
                        Cancel
                      </v-btn>
                      <v-btn
                        color="primary"
                        @click="save"
                        class="mb-4 mr-4"
                        :disabled="disabled"
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
            :items="branches"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="userPermissions.branch_list"
          >
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editBranch(item)"
                v-if="userPermissions.branch_edit"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="userPermissions.branch_delete"
              >
                mdi-delete
              </v-icon>
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
    editedBranch: {
      code: { required },
      name: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "name" },
        { text: "Branch Code", value: "code" },
        { text: "Actions", value: "actions", sortable: false },
      ],
      switch1: true,
      disabled: false,
      dialog: false,
      branches: [],
      editedIndex: -1,
      editedBranch: {
        name: "",
        code: "",
      },
      defaultItem: {
        name: "",
        code: "",
      },
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/dashboard",
        },
        {
          text: "Branch Lists",
          disabled: true,
        },
      ],
      loading: true,
    };
  },

  methods: {
    getBranch() {
      this.loading = true;
      axios.get("/api/branch/index").then(
        (response) => {
          this.branches = response.data.branches;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    editBranch(item) {
      this.editedIndex = this.branches.indexOf(item);
      this.editedBranch = Object.assign({}, item);
      this.dialog = true;
    },

    deleteBranch(branch_id) {
      const data = { branch_id: branch_id };
      this.loading = true;
      axios.post("/api/branch/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "branch-delete" });
          }
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

          const branch_id = item.id;
          const index = this.branches.indexOf(item);

          //Call delete Branch function
          this.deleteBranch(branch_id);

          //Remove item from array branches
          this.branches.splice(index, 1);

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
        this.editedBranch = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    save() {
      this.$v.$touch();

      if (!this.$v.$error) {
        this.disabled = true;

        if (this.editedIndex > -1) {
          const data = this.editedBranch;
          const branch_id = this.editedBranch.id;

          axios.post("/api/branch/update/" + branch_id, data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "branch-edit" });

                Object.assign(
                  this.branches[this.editedIndex],
                  this.editedBranch
                );
                this.showAlert();
                this.close();
              }

              this.disabled = false;
            },
            (error) => {
              this.isUnauthorized(error);
              this.disabled = false;
            }
          );
        } else {
          const data = this.editedBranch;

          axios.post("/api/branch/store", data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "branch-create" });

                this.showAlert();
                this.close();

                //push recently added data from database
                this.branches.push(response.data.branch);
              }
              this.disabled = false;
            },
            (error) => {
              this.isUnauthorized(error);
              this.disabled = false;
            }
          );
        }
      }
    },

    clear() {
      this.$v.$reset();
      this.editedBranch.name = "";
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
          action == "user-edit" ||
          action == "role-edit" ||
          action == "role-delete" ||
          action == "permission-create" ||
          action == "permission-delete"
        ) {
          this.userRolesPermissions();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Branch" : "Edit Branch";
    },
    branchErrors() {
      const errors = [];
      if (!this.$v.editedBranch.name.$dirty) return errors;
      !this.$v.editedBranch.name.required &&
        errors.push("Branch is required.");
      return errors;
    },
    branchCodeErrors() {
      const errors = [];
      if (!this.$v.editedBranch.code.$dirty) return errors;
      !this.$v.editedBranch.code.required &&
        errors.push("Branch Code is required.");
      return errors;
    },
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getBranch();
    // this.websocket();
  },
};
</script>