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
            Access Chart Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="userPermissions.access_chart_list"
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
                  v-if="userPermissions.access_chart_create"
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
                            <v-autocomplete
                              v-model="editedItem.access_for"
                              :items="access_modules"
                              item-text="name"
                              item-value="id"
                              label="Access For"
                              :error-messages="accessForErrors"
                              @input="$v.editedItem.access_for.$touch()"
                              @blur="$v.editedItem.access_for.$touch()"
                            >
                            </v-autocomplete>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="mt-0 mb-0 pt-0 pb-0">
                            <v-text-field
                              name="name"
                              v-model="editedItem.name"
                              label="Name"
                              required
                              :error-messages="nameErrors + nameError.name"
                              @input="
                                $v.editedItem.name.$touch() +
                                  (nameError.name = [])
                              "
                              @blur="$v.editedItem.name.$touch()"
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
            :items="access_charts"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="userPermissions.access_chart_list"
          >
            <template v-slot:item.access_chart_user_maps.length="{ item }">
              <v-chip
                :class="
                  item.access_chart_user_maps.length ? 'success' : 'error'
                "
              >
                {{ item.access_chart_user_maps.length }}
              </v-chip>
            </template>
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="info"
                @click="viewApprovingOfficer(item)"
                v-if="userPermissions.approving_officer_list"
              >
                mdi-eye
              </v-icon>
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editAccessChart(item)"
                v-if="userPermissions.access_chart_edit"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="userPermissions.access_chart_delete"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-data-table>
          <v-dialog
            v-model="dialog2"
            fullscreen
            hide-overlay
            transition="dialog-bottom-transition"
            scrollable
          >
            <v-card>
              <v-card-title>
                <v-btn icon @click="close" class="mr-4 white" x-large>
                  <v-icon>mdi-close-circle</v-icon>
                </v-btn>
                {{ editedItem.name + " - Access Chart" }}
              </v-card-title>
              <v-divider class="ma-0"></v-divider>
              <v-card-text>
                <v-row>
                  <v-col class="mt-4">
                    <v-card>
                      <v-card-title>
                        Approving Officers
                        <v-btn
                          class="primary ml-4"
                          small
                          @click="newApprovingOfficer()"
                          v-if="userPermissions.approving_officer_list"
                        >
                          <v-icon> mdi-plus</v-icon> New
                        </v-btn>
                      </v-card-title>
                      <v-divider class="ma-0"></v-divider>
                      <v-card-text>
                        <v-data-table
                          :headers="headers2"
                          :items="approving_officers"
                          :loading="loading"
                          loading-text="Loading... Please wait"
                          hide-default-footer
                        >
                          <template
                            v-slot:item.approving_officer="{ item, index }"
                          >
                            <span
                              v-if="
                                editedIndex2 === -1 || editedIndex2 !== index
                              "
                            >
                              {{
                                item.status !== "New"
                                  ? item.user
                                    ? "Approving Officer " +
                                      item.access_level +
                                      " - " +
                                      item.user.name
                                    : "No User"
                                  : ""
                              }}
                            </span>
                            <v-row
                              v-if="
                                index === editedIndex2
                                  ? true
                                  : false || item.status === 'New'
                              "
                            >
                              <v-col class="pb-0">
                                <v-autocomplete
                                  v-model="approvingOfficer.user_id"
                                  :items="filteredUsers"
                                  item-text="name"
                                  item-value="id"
                                  label="User"
                                  :error-messages="approvingOfficerErrors"
                                  @input="$v.approvingOfficer.user_id.$touch()"
                                  @blur="$v.approvingOfficer.user_id.$touch()"
                                  hide-details=""
                                >
                                </v-autocomplete>
                              </v-col>
                              <v-col class="pb-0">
                                <v-autocomplete
                                  v-model="approvingOfficer.access_level"
                                  :items="accessLevels"
                                  item-text="level"
                                  label="Access Level"
                                >
                                </v-autocomplete>
                              </v-col>
                            </v-row>
                          </template>

                          <template v-slot:item.actions="{ item, index }">
                            <v-icon
                              small
                              class="mr-2"
                              color="green"
                              @click="editApprover(item)"
                              v-if="
                                userPermissions.access_chart_edit &&
                                index !== editedIndex2 &&
                                item.status !== 'New'
                              "
                              :disabled="addEditMode === 'Add' ? true : false"
                            >
                              mdi-pencil
                            </v-icon>

                            <v-icon
                              small
                              color="red"
                              @click="
                                showConfirmAlert(item, 'Approving Officer')
                              "
                              v-if="
                                userPermissions.access_chart_delete &&
                                index !== editedIndex2 &&
                                item.status !== 'New'
                              "
                              :disabled="['Add', 'Edit'].includes(addEditMode)"
                            >
                              mdi-delete
                            </v-icon>

                            <v-btn
                              class="primary"
                              x-small
                              v-if="
                                index === editedIndex2
                                  ? true
                                  : false || item.status === 'New'
                              "
                              :disabled="disabled"
                              @click="saveApprovingOfficer()"
                            >
                              save
                            </v-btn>

                            <v-btn
                              x-small
                              v-if="
                                index === editedIndex2
                                  ? true
                                  : false || item.status === 'New'
                              "
                              color="#E0E0E0"
                              @click="cancelEvent(item)"
                            >
                              cancel
                            </v-btn>
                          </template>
                        </v-data-table>
                      </v-card-text>
                    </v-card>
                  </v-col>
                </v-row>
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
    editedItem: {
      access_for: { required },
      name: { required },
    },
    approvingOfficer: {
      user_id: { required },
      access_level: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Access Chart Name", value: "name" },
        { text: "Access For", value: "access_module.name" },
        { text: "Approving Officers", value: "access_chart_user_maps.length" },
        { text: "Actions", value: "actions", sortable: false, width: "120px" },
      ],
      headers2: [
        { text: "Approving Officer", value: "approving_officer" },
        { text: "Actions", value: "actions", sortable: false, width: "120px" },
      ],
      disabled: false,
      dialog: false,
      dialog2: false,
      access_modules: [],
      access_charts: [],
      access_levels: [],
      access_level: "",
      users: [],
      editedIndex: -1,
      editedIndex2: -1,
      editedItem: {
        access_for: "",
        name: "",
      },
      defaultItem: {
        access_for: "",
        name: "",
      },
      approvingOfficer: {
        user_id: "",
        access_level: 1,
      },
      approving_officers: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Access Chart Lists",
          disabled: true,
        },
      ],
      loading: true,
      nameError: {
        name: [],
      },
      addEditMode: "",
      max_access_level: "",
    };
  },

  methods: {
    getAccessChart() {
      this.loading = true;
      axios.get("/api/access_chart/index").then(
        (response) => {
          let data = response.data;

          this.access_charts = data.access_charts;
          this.access_modules = data.access_modules;
          this.access_level = data.access_level.level;
          this.users = data.users;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    editAccessChart(item) {
      this.editedIndex = this.access_charts.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteAccessChart(access_chart_id) {
      const data = { access_chart_id: access_chart_id };
      this.loading = true;
      axios.post("/api/access_chart/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "access-chart-delete" });
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

    showConfirmAlert(item, doctype) {
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

          if (doctype === "Access Chart") {
            const access_chart_id = item.id;
            const index = this.access_charts.indexOf(item);

            //Call delete Access Chart function
            this.deleteAccessChart(access_chart_id);

            //Remove item from array permissions
            this.access_charts.splice(index, 1);
          } else {

            const approver_id = item.id;
            const index = this.approving_officers.indexOf(item);

            //Remove item from array approving_officers
            this.approving_officers.splice(index, 1);
            
            this.access_charts[this.editedIndex].access_chart_user_maps.splice(
              index,
              1
            );

            this.deleteApprover(approver_id);
          }

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
      this.dialog2 = false;
      this.access_levels = [];
      this.clear();
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });

      this.approvingOfficer = Object.assign(
        {},
        { user_id: "", access_level: 1 }
      );
      this.approving_officers = [];
      this.editedIndex2 = -1;
    },

    save() {
      this.$v.editedItem.$touch();
      this.accessChartError = {
        name: [],
      };

      if (!this.$v.editedItem.$error) {
        this.disabled = true;

        if (this.editedIndex > -1) {
          const data = this.editedItem;
          const access_chart_id = this.editedItem.id;

          axios.post("/api/access_chart/update/" + access_chart_id, data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "access-chart-edit" });

                Object.assign(
                  this.access_charts[this.editedIndex],
                  this.editedItem
                );
                this.showAlert();
                this.close();
              } else {
                let errors = response.data;
                let errorNames = Object.keys(response.data);

                errorNames.forEach((value) => {
                  this.accessChartError[value].push(errors[value]);
                });
              }

              this.disabled = false;
            },
            (error) => {
              this.isUnauthorized(error);
              this.disabled = false;
            }
          );
        } else {
          const data = this.editedItem;

          axios.post("/api/access_chart/store", data).then(
            (response) => {
              console.log(response);
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "access-chart-create" });

                this.showAlert();
                this.close();

                //push recently added data from database
                this.access_charts.push(response.data.access_chart);
              } else {
                let errors = response.data;
                let errorNames = Object.keys(response.data);

                errorNames.forEach((value) => {
                  this.accessChartError[value].push(errors[value]);
                });
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

    viewApprovingOfficer(item) {
      this.editedIndex = this.access_charts.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog2 = true;

      let approving_officers = item.access_chart_user_maps;

      approving_officers.forEach((value) => {
        this.approving_officers.push(value);
      });
    },
    saveApprovingOfficer() {
      this.$v.approvingOfficer.$touch();
      if (!this.$v.approvingOfficer.$error) {
        this.disabled = true;

        if (this.editedIndex2 > -1) {
          const data = this.approvingOfficer;
          const approver_id = this.approvingOfficer.id;

          axios
            .post("/api/access_chart_user_map/update/" + approver_id, data)
            .then(
              (response) => {
                console.log(response.data);
                if (response.data.success) {
                  // send data to Sockot.IO Server
                  // this.$socket.emit("sendData", { action: "approving-officer-edit" });

                  let index = this.editedIndex2;

                  Object.assign(
                    this.approving_officers[index],
                    response.data.approver
                  );

                  Object.assign(
                    this.access_charts[this.editedIndex].access_chart_user_maps[
                      index
                    ],
                    response.data.approver
                  );

                  this.clearApprovingOfficer();

                  this.showAlert();
                } else {
                  let errors = response.data;
                  let errorNames = Object.keys(response.data);
                }

                this.disabled = false;
              },
              (error) => {
                this.isUnauthorized(error);
                this.disabled = false;
              }
            );
        } else {
          const data = this.approvingOfficer;

          data["access_chart_id"] = this.editedItem.id;

          axios.post("/api/access_chart_user_map/store", data).then(
            (response) => {
              console.log(response);
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "approving-officer-create" });

                this.showAlert();

                //push recently added data from database

                let index = this.approving_officers.length - 1;

                this.approving_officers.splice(
                  index,
                  1,
                  response.data.approver
                );

                this.access_charts[
                  this.editedIndex
                ].access_chart_user_maps.push(response.data.approver);

                this.clearApprovingOfficer();
              } else {
                let errors = response.data;
                let errorNames = Object.keys(response.data);
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

    newApprovingOfficer() {
      this.clearApprovingOfficer();

      this.addEditMode = "Add";

      let hasNew = false;

      this.approving_officers.forEach((value, index) => {
        if (value.status === "New") {
          hasNew = true;
        }
      });

      if (!hasNew) {
        this.approving_officers.push({ status: "New" });
      }
    },

    editApprover(item) {
      this.addEditMode = "Edit";
      this.editedIndex2 = this.approving_officers.indexOf(item);
      this.approvingOfficer = Object.assign(
        {},
        { id: item.id, user_id: item.user.id, access_level: item.access_level }
      );
    },

    cancelEvent(item) {
      this.editedIndex2 = this.approving_officers.indexOf(item);
      if (this.addEditMode === "Edit") {
        this.editedIndex2 = -1;
      } else {
        this.approving_officers.splice(this.editedIndex2, 1);
      }

      this.addEditMode = "";
    },

    clearApprovingOfficer() {
      this.$v.approvingOfficer.$reset();
      this.addEditMode = "";
      this.disabled = false;
      this.editedIndex2 = -1;
      this.approvingOfficer = Object.assign(
        {},
        {
          user_id: "",
          access_level: 1,
        }
      );
    },

    deleteApprover(approver_id) {
      const data = { approver_id: approver_id };
      this.loading = true;
      axios.post("/api/access_chart_user_map/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "approving-officer-delete" });
            Object.assign(
              this.access_charts[this.editedIndex].access_chart_user_maps,
              this.approving_officers
            );
          }
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    clear() {
      this.$v.editedItem.$reset();
      this.editedItem.name = "";
      this.nameError = {
        name: [],
      };
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
          action == "access-chart-create" ||
          action == "access-chart-edit" ||
          action == "access-chart-delete"
        ) {
          this.getAccessChart();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Access Chart" : "Edit Access Chart";
    },
    nameErrors() {
      const errors = [];
      if (!this.$v.editedItem.name.$dirty) return errors;
      !this.$v.editedItem.name.required && errors.push("Name is required.");
      return errors;
    },
    accessForErrors() {
      const errors = [];
      if (!this.$v.editedItem.access_for.$dirty) return errors;
      !this.$v.editedItem.access_for.required &&
        errors.push("Access Module is required.");
      return errors;
    },
    approvingOfficerErrors() {
      const errors = [];
      if (!this.$v.approvingOfficer.user_id.$dirty) return errors;
      !this.$v.approvingOfficer.user_id.required &&
        errors.push("User is required.");
      return errors;
    },
    filteredUsers() {
      let users = [];
      let userIdArr = [];

      // push all user id into array variable
      this.approving_officers.forEach((value) => {
        userIdArr.push(value.user_id);
      });

      this.users.forEach((value) => {
        if (!userIdArr.includes(value.id)) {
          users.push(value);
        }

        // if edit mode
        if (
          this.editedIndex2 > -1 &&
          this.approvingOfficer.user_id === value.id
        ) {
          users.push(value);
        }
      });

      return users;
    },
    accessLevels() {
      let accessLevelArr = [];
      let access_levels = [];
      let max_access_level = 1;

      this.approving_officers.forEach((value) => {
        // if has access level
        if(value.access_level)
        {
          accessLevelArr.push(value.access_level);
        }
      });

      max_access_level = Math.max(...accessLevelArr);

      // if access_level from DB is less than max_access_level from approving_officers
      if (this.access_level > max_access_level) {
        max_access_level = max_access_level + 1;
         
      } else {
        max_access_level = this.access_level;
      }

      for (let ctr = 1; ctr <= max_access_level; ctr++) {
        access_levels.push(ctr);
      }
      return access_levels;
    },
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getAccessChart();
    // this.websocket();
  },
};
</script>