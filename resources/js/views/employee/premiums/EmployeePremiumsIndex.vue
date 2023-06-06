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
        <!-- <div class="d-flex justify-content-end mb-3">
          <div>
            <v-btn
              color="success"
              small
              @click="exportData()"
              v-if="hasPermission('employee-premiums-export', 'employee-premiums-list-all')"
            >
              <v-icon class="mr-1" small> mdi-microsoft-excel </v-icon>
              Export All Data
            </v-btn>
          </div>
        </div> -->
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
            group-by="name"
            class="elevation-1"
            :expanded.sync="expanded"
            loading-text="Loading... Please wait"
            v-if="hasPermission('employee-premiums-list')"
          >
            <template v-slot:item.date_uploaded="{ item }">
              <v-chip color="secondary" v-if="item.date_uploaded">
                {{ item.date_uploaded }}
              </v-chip>
            </template>
            <template
              v-slot:group.header="{
                items,
                headers,
                toggle,
                isOpen,
              }"
            >
              <td colspan="3">
                <v-row>
                  <v-col>
                    <v-btn
                      @click="toggle"
                      small
                      icon
                      :ref="items"
                      :data-open="isOpen"
                    >
                      <v-icon v-if="isOpen">mdi-chevron-up</v-icon>
                      <v-icon v-else>mdi-chevron-down</v-icon>
                    </v-btn>
                    <!-- <v-chip color="secondary">
                      <strong></strong>
                    </v-chip> -->
                    {{ items[0].name }}
                  </v-col>
                </v-row>
              </td>
              <td> 
                <v-btn x-small color="primary" @click="importExcel(items)" v-if="hasPermission('employee-premiums-import')"> 
                  <v-icon small class="mr-2">mdi-upload</v-icon> import
                </v-btn> 
              </td>
            </template>
            <template v-slot:item="{ item }">
              <tr v-for="(value, index) in item.file_upload_logs">
                <td> </td>
                <td>
                  <v-chip color="secondary">
                    {{ value.date_uploaded }}
                  </v-chip>
                </td>
                <td> 
                  <v-chip color="secondary">
                    {{ value.docdate }}
                  </v-chip> </td>
                <td>
                  <v-menu offset-y>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn x-small v-bind="attrs" v-on="on" class="primary--text">
                        Actions
                        <v-icon small> mdi-menu-down </v-icon>
                      </v-btn>
                    </template>
                    <v-list class="pa-1">
                      <v-list-item
                        class="ma-0 pa-0"
                        style="min-height: 25px"
                      >
                        <v-list-item-title>
                          <v-btn 
                            x-small 
                            @click="viewList(value)" 
                            class="mx-1"
                            width="100px"
                            color="info"
                          >
                            <v-icon class="mr-1" x-small>
                              mdi-eye
                            </v-icon>
                            View
                          </v-btn>
                        </v-list-item-title>
                      </v-list-item>
                      <v-list-item
                        class="ma-0 pa-0"
                        style="min-height: 25px"
                        v-if="hasPermission('employee-premiums-export')"
                      >
                        <v-list-item-title>
                          <v-btn
                            class="mx-1"
                            x-small
                            @click="exportData(value)"
                            width="100px"
                            color="success"
                          >
                            <v-icon class="mr-1" x-small> mdi-microsoft-excel </v-icon>
                            Export
                          </v-btn>
                        </v-list-item-title>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </td>
              </tr>
            </template>
          </v-data-table>
        </v-card>
        <ImportDialog 
            :api_route="api_route" 
            :dialog_import="dialog_import"
            @getData="getEmployeePremiums"
            @closeImportDialog="closeImportDialog"
          />
      </v-main>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState, mapGetters } from "vuex";
import ImportDialog from "../components/ImportDialog.vue";

export default {
  name: "EmployeePremiumsIndex",
  components: {
    ImportDialog,
  },
  props: {

  },
  mixins: [validationMixin],
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "branch" },
        { text: "Date Uploaded", value: "date_uploaded" },
        { text: "Document Date", value: "docdate" },
        { text: "Actions", value: "actions", sortable: false, width: "120px" },
      ],
      disabled: false,
      expanded: [],
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
      branch_id: "",
      dialog_import: false,
      api_route: "",
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

      let branch_id = item.branch_id;

      this.$router.push({
        name: 'employee.premiums.list.view',
        params: { branch_id: branch_id, file_upload_log_id: item.id }
      });

    },

    importExcel(item) {
      this.branch_id = item[0].id;
      this.dialog_import = true;
      this.api_route = 'api/employee_premiums/import_premiums/' + this.branch_id;
    },

    exportData(item) {
      // window.open(
      //   location.origin + "/api/employee_premiums/export_premiums/" + 0,
      //   "_blank"
      // );

      window.open(
        location.origin + "/api/employee_premiums/export_premiums/" + item.id,
        "_blank"
      );
    },

    closeImportDialog() {
      this.branch_id = "";
      this.dialog_import = false;
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
        if (this.hasPermission('employee-premiums-list-all')) {
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
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission","hasAnyPermission"]),
  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getEmployeePremiums();
    // this.websocket();
  },
};
</script>