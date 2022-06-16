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
            Tactical Requisition Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="userPermissions.tactical_requisition_list"
            ></v-text-field>
            <template>
              <v-toolbar flat>
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  fab
                  dark
                  class="mb-2"
                  @click="createTacticalRequisition()"
                  v-if="userPermissions.tactical_requisition_create"
                >
                  <v-icon>mdi-plus</v-icon>
                </v-btn>
              </v-toolbar>
            </template>
          </v-card-title>
          {{ max_level_approver }}
          <div v-for="(item, index) in max_level_approver">
            <v-icon color="success" small>mdi-checkbox-marked-circle</v-icon>
          </div>
          <v-data-table
            :headers="headers"
            :items="tactical_requisitions"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="userPermissions.tactical_requisition_list"
          >
            <template v-slot:item.progress="{ item }">
              <template v-for="(item, index) in max_approver_level">
                <v-tooltip top color="success">
                  <template v-slot:activator="{ on, attrs }">
                    <v-icon color="success" v-bind="attrs" v-on="on">mdi-checkbox-marked-circle</v-icon>
                  </template>
                  <span>Top tooltip</span>
                </v-tooltip>
              </template>
            </template>
            <template v-slot:item.status="{ item }">
              <v-chip
                :color="item.status === 'Pending' ? 'warning' : 'success'"
              >
                {{ item.status }}
              </v-chip>
            </template>
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="info"
                @click="viewTacticalRequisition(item)"
                v-if="userPermissions.tactical_requisition_edit"
              >
                mdi-eye
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="userPermissions.tactical_requisition_delete"
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
        { text: "Event Title", value: "marketing_event.event_name" },
        { text: "Branch", value: "branch.name" },
        { text: "Progress", value: "progress" },
        { text: "Status", value: "status" },
        { text: "Date Created", value: "date_created" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],

      disabled: false,
      dialog: false,
      tactical_requisitions: [],
      editedIndex: -1,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Tactical Requisition Lists",
          disabled: true,
        },
      ],
      loading: true,
      expenseError: {
        description: [],
      },
      max_level_approver: "",
    };
  },

  methods: {
    getTacticalRequisition() {
      this.loading = true;
      axios.get("/api/tactical_requisition/index").then(
        (response) => {
          this.tactical_requisitions = response.data.tactical_requisitions;
          this.max_approver_level = response.data.max_approver_level;
          console.log(response);
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    viewTacticalRequisition(item) {
      this.$router.push({
        name: "tactical.view",
        params: { tactical_requisition_id: item.id },
      });
    },

    deleteTacticalRequisition(tactical_requisition_id) {
      const data = { tactical_requisition_id: tactical_requisition_id };
      this.loading = true;
      axios.post("/api/tactical_requisition/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "tactical-requisition-delete" });
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

          const tactical_requisition_id = item.id;
          const index = this.tactical_requisitions.indexOf(item);

          //Call delete Brand function
          this.deleteTacticalRequisition(tactical_requisition_id);

          //Remove item from array tactical_requisitions
          this.tactical_requisitions.splice(index, 1);

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

    createTacticalRequisition() {
      this.$router.push({ name: "tactical.create" });
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
          action == "tactical-requisition-create" ||
          action == "tactical-requisition-edit" ||
          action == "tactical-requisition-delete"
        ) {
          this.getTacticalRequisition();
        }
      };
    },
  },
  computed: {
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getTacticalRequisition();
    // this.websocket();
  },
};
</script>