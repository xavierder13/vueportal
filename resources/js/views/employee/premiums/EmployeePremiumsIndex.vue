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
            <v-btn
              color="success"
              small
              @click="exportData()"
              v-if="userPermissions.employee_premiums_export && userPermissions.employee_premiums_list_all"
            >
              <v-icon class="mr-1" small> mdi-microsoft-excel </v-icon>
              Export All Data
            </v-btn>
          </div>
        </div>
        <v-card>
          <v-card-title>
            Branches 
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
            ></v-text-field>
            <v-spacer></v-spacer>
          </v-card-title>

          <v-data-table
            :headers="headers"
            :items="filteredBranches"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="userPermissions.employee_premiums_list"
          >
            <template v-slot:item.last_upload="{ item }">
              <v-chip color="secondary" v-if="item.last_upload">
                {{ item.last_upload }}
              </v-chip>
            </template>
            <template v-slot:item.actions="{ item }">
              <v-btn color="info" x-small @click="viewList(item)">
                <v-icon small class="mr-1" >
                  mdi-eye
                </v-icon>
                View
              </v-btn>
              
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
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "name" },
        { text: "Last Uploaded", value: "last_upload" },
        { text: "Actions", value: "actions", sortable: false, width: "120px" },
      ],
      disabled: false,
      dialog: false,
      employees: [],
      branches: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Employee Premiums Lists",
          disabled: false,
        },
      ],
      loading: true,
    };
  },

  methods: {
    getEmployeePremiums() {
      this.loading = true;
      axios.get("/api/employee_premiums/index").then(
        (response) => {

          this.branches = response.data.branches;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    viewList(item) {
      let branch_id = item.id;
    
      this.$router.push({
        name: 'employee.premiums.list.view',
        params: { branch_id: branch_id }
      });

    },

    exportData() {
      window.open(
        location.origin + "/api/employee_premiums/export_premiums/" + 0,
        "_blank"
      );
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

        if (action == "employee-premiums-import") {
          this.getEmployeePremiums();
        }
      };
    },
  },
  computed: {
    filteredBranches() {
      let branches = [];

      this.branches.forEach((value) => {
        if (this.userPermissions.employee_premiums_list_all) {
          branches.push(value);
        } else {
           if(value.id === this.user.branch_id)
           {
             branches.push(value);
           }
        }
      });

      return branches;
    },
    
    ...mapState("auth", ["user"]),
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getEmployeePremiums();
    // this.websocket();
  },
};
</script>