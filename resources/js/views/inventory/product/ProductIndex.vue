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
        <DataTableGroup
          :branches="filteredBranches"
          :loading="loading"
          :canViewList="hasPermission('product-list')"
          :canImport="hasPermission('product-import')"
          :canExport="hasPermission('product-export')"
        />
        
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
import ImportDialog from "../../components/ImportDialog.vue";
import DataTableGroupVue from "../../components/DataTableGroup.vue";

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
     
      const data = { file_upload_log_id: item.id }
      axios.post('/api/employee_premiums/export_premiums', data, { responseType: 'arraybuffer'})
        .then((response) => {
            var fileURL = window.URL.createObjectURL(new Blob([response.data]));
            var fileLink = document.createElement('a');
            fileLink.href = fileURL;
            fileLink.setAttribute('download', 'EmployeePremiums.xls');
            document.body.appendChild(fileLink);
            fileLink.click();
        }, (error) => {
          console.log(error);
        }
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