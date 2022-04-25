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
            Marketing Event Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="userPermissions.marketing_event_list"
            ></v-text-field>
            <template>
              <v-toolbar flat>
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  fab
                  dark
                  class="mb-2"
                  @click="createMarketingEvent()"
                  v-if="userPermissions.marketing_event_create"
                >
                  <v-icon>mdi-plus</v-icon>
                </v-btn>
              </v-toolbar>
            </template>
          </v-card-title>

          <v-data-table
            :headers="headers"
            :items="marketing_events"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="userPermissions.marketing_event_list"
          >
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editMarketingEvent(item)"
                v-if="userPermissions.permission_edit"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="userPermissions.marketing_event_delete"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-data-table>
          <!-- <v-row>
            <v-col>
              <v-treeview
                :items="filteredElements"
                :search="search"
                open-all
                class="ma-6"
              >
                <template v-slot:label="{ item }" class="pa-6">
                  <v-btn
                    color="primary"
                    @click="addItem(item)"
                    icon
                    v-if="item.children"
                    :class="item.field === 'expense_particular' ? 'ml-6' : ''"
                  >
                    <v-icon>mdi-plus-circle</v-icon>
                  </v-btn>
                  <v-btn
                    color="red"
                    @click="removeItem(item)"
                    icon
                    :class="
                      item.field === 'expense_sub_particular' ? 'ml-16' : ''
                    "
                  >
                    <v-icon class="ma-0 pa-0">mdi-minus-circle</v-icon>
                  </v-btn>
                  <span
                    :class="
                      item.field === 'event_name'
                        ? 'h3'
                        : item.field === 'expense_particular'
                        ? 'subtitle-1'
                        : 'subtitle-2'
                    "
                  >
                    {{ item.name }}
                  </span>
                </template>
              </v-treeview>
            </v-col>
          </v-row> -->
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
      description: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        // { text: "Event Title", value: "event_name" },
        { text: "Event Title", value: "event_name" },
        { text: "Active", value: "active" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      expanded: [],
      switch1: true,
      disabled: false,
      dialog: false,
      marketing_events: [],
      editedIndex: -1,
      editedData: "",
      editedItem: {
        description: "",
        active: "Y",
      },
      defaultItem: {
        description: "",
        active: "Y",
      },
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Marketing Event Lists",
          disabled: true,
        },
      ],
      loading: true,
      expenseError: {
        description: [],
      },
    };
  },

  methods: {
    getMarketingEvent() {
      this.loading = true;
      axios.get("/api/marketing_event/index").then(
        (response) => {
          this.marketing_events = response.data.marketing_events;
          console.log(response);
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    editMarketingEvent(item) {
      this.$router.push({
        name: "marketing.event.edit",
        params: { marketing_event_id: item.id }
      });
    },

    deleteMarketingEvent(expense_id) {
      const data = { expense_id: expense_id };
      this.loading = true;
      axios.post("/api/marketing_event/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "marketing-event-delete" });
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

          const expense_id = item.id;
          const index = this.marketing_events.indexOf(item);

          //Call delete Brand function
          this.deleteMarketingEvent(expense_id);

          //Remove item from array marketing_events
          this.marketing_events.splice(index, 1);

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
      this.expenseError = {
        description: [],
      };

      if (!this.$v.$error) {
        this.disabled = true;

        if (this.editedIndex > -1) {
          const data = this.editedItem;
          const expense_id = this.editedItem.id;

          axios.post("/api/marketing_event/update/" + expense_id, data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "marketing-event-edit" });

                Object.assign(
                  this.marketing_events[this.editedIndex],
                  this.editedItem
                );
                this.showAlert();
                this.close();
              } else {
                let errors = response.data;
                let errorNames = Object.keys(response.data);

                errorNames.forEach((value) => {
                  this.expenseError[value].push(errors[value]);
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

          axios.post("/api/marketing_event/store", data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "marketing-event-create" });

                this.showAlert();
                this.close();

                //push recently added data from database
                this.marketing_events.push(response.data.marketing_event);
              } else {
                let errors = response.data;
                let errorNames = Object.keys(response.data);

                errorNames.forEach((value) => {
                  this.expenseError[value].push(errors[value]);
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

    addItem(item) {
      let parent_index = item.parent_index;
      let row_index = -1;
      let marketing_event = this.marketing_events[parent_index];
      let expense_particulars;
      let expense_sub_particulars;

      if (item.row_index > -1) {
        row_index = item.row_index;
        expense_particulars = marketing_event.expense_particulars[row_index];
        expense_sub_particulars = expense_particulars.expense_sub_particulars;

        expense_sub_particulars.push({
          description: "",
          newDescription: true,
        });
      } else {
        marketing_event.expense_particulars.push({
          description: "",
          expense_sub_particulars: [],
          newDescription: true,
        });
        console.log(marketing_event);
      }
    },

    removeItem() {
      this.showConfirmAlert();
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

          const position_id = item.id;
          const index = this.positions.indexOf(item);

          //Call delete Position function
          this.removeItem(position_id);

          //Remove item from array positions
          this.positions.splice(index, 1);

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

    createMarketingEvent() {
      this.$router.push({ name: "marketing.event.create" });
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
          action == "marketing-event-create" ||
          action == "marketing-event-edit" ||
          action == "marketing-event-delete"
        ) {
          this.getMarketingEvent();
        }
      };
    },
  },
  computed: {
    treeView() {
      let marketing_events = [];

      this.marketing_events.forEach((value, index) => {
        marketing_events.push({
          parent_index: index,
          id: value.id,
          name: value.event_name,
          field: "event_name",
          children: [],
        });
        value.expense_particulars.forEach((val, i) => {
          marketing_events[index].children.push({
            parent_index: index,
            row_index: i,
            id: val.id,
            name: val.description,
            field: "expense_particular",
            children: [],
          });

          val.expense_sub_particulars.forEach((v, j) => {
            marketing_events[index].children[i].children.push({
              parent_index: index,
              row_index: i,
              sub_row_index: j,
              id: v.id,
              name: v.description,
              expense_sub_particular: v.description,
              field: "expense_sub_particular",
            });
          });
        });
      });

      return marketing_events;
    },
    filteredElements() {
      return this.treeView.reduce((acc, curr) => {
        console.log("curr", curr);
        console.log("acc", acc);
        const childrenContain = curr.children.filter((child) => {
          const index = child.name.toLowerCase().indexOf(this.search) >= 0;
          console.log("index", index);
          return index;
        });
        console.log("childrenContain", childrenContain);
        // return childrenContain
        if (childrenContain.length) {
          acc.push({
            ...curr,
            children: [...childrenContain],
          });
        }

        return acc;
      }, []);
    },
    formTitle() {
      return this.editedIndex === -1
        ? "New Expense Particular"
        : "Edit Expense Particular";
    },
    expenseErrors() {
      const errors = [];
      if (!this.$v.editedItem.description.$dirty) return errors;
      !this.$v.editedItem.description.required &&
        errors.push("Description is required.");
      return errors;
    },
    activeStatus() {
      if (this.switch1) {
        this.editedItem.active = "Y";
        return " Active";
      } else {
        this.editedItem.active = "N";
        return " Inactive";
      }
    },
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getMarketingEvent();
    // this.websocket();
  },
};
</script>