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
          <v-data-table
            :headers="headers"
            :items="tacticalRequisitions"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="userPermissions.tactical_requisition_list"
          >
            <template v-slot:item.progress="{ item }">
              <template v-for="(item, index) in item.approval_progress">
                
                <v-tooltip top :color=" item.done ? 'success' : '' " v-if="item.approver.length">
                  <template v-slot:activator="{ on, attrs }">
                    <v-icon :color=" item.done ? 'success' : '' " v-bind="attrs" v-on="on">mdi-checkbox-marked-circle</v-icon>
                  </template>
                  <span>{{ item.approver.join(', ') }}</span>
                </v-tooltip>
                
                <!-- show check icon without tooltip -->
                <v-icon v-if="!item.approver.length">mdi-checkbox-marked-circle</v-icon>

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
                v-if="userPermissions.tactical_requisition_edit || userPermissions.tactical_requisition_approve"
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
        { text: "Created By", value: "user.name" },
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
      approval_progress: [],
    };
  },

  methods: {
    getTacticalRequisition() {
      this.loading = true;
      axios.get("/api/tactical_requisition/index").then(
        (response) => {
          
          let data = response.data
        
          this.tactical_requisitions = data.tactical_requisitions;
          this.approval_progress = data.approval_progress

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
          console.log(response);
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
    tacticalRequisitions()
    {
      let tactical_requisitions = [];
      this.tactical_requisitions.forEach((value, i) => {
        tactical_requisitions.push(value);
        this.approval_progress.forEach((val) => {
          if(value.id === val.tactical_requisition_id)
          {
            tactical_requisitions[i]['approval_progress'] = val.progress;
          }
        });
      });

      return tactical_requisitions;
    },
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