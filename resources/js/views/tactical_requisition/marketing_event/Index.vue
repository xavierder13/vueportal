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
                v-if="userPermissions.marketing_event_edit"
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

      disabled: false,
      dialog: false,
      marketing_events: [],
      editedIndex: -1,
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

    deleteMarketingEvent(marketing_event_id) {
      const data = { marketing_event_id: marketing_event_id };
      this.loading = true;
      axios.post("/api/marketing_event/delete", data).then(
        (response) => {
          console.log(response);
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

          const marketing_event_id = item.id;
          const index = this.marketing_events.indexOf(item);

          //Call delete Brand function
          this.deleteMarketingEvent(marketing_event_id);

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