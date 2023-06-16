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
        <div class="d-flex justify-content-end mb-3" v-if="hasAnyPermission('inventory-recon-create', 'inventory-recon-sync')">
          <div>
            <v-menu offset-y>
              <template v-slot:activator="{ on, attrs }">
                <v-btn small v-bind="attrs" v-on="on" color="primary">
                  Actions
                  <v-icon small> mdi-menu-down </v-icon>
                </v-btn>
              </template>
              <v-list class="pa-1">
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="hasPermission('inventory-recon-create')"
                >
                  <v-list-item-title>
                    <v-btn
                      color="primary"
                      class="mx-1"
                      width="100px"
                      x-small
                      @click="openImportDialog('import')"
                    >
                      <v-icon class="mr-1" x-small> mdi-import </v-icon>
                      Import
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="hasPermission('inventory-recon-sync')"
                >
                  <v-list-item-title>
                    <v-btn
                      color="info"
                      class="mx-1"
                      width="100px"
                      x-small
                      @click="openImportDialog('sync')"
                    >
                      <v-icon class="mr-1" x-small> mdi-sync </v-icon>
                      Sync
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </div>
        </div>
        <v-card>
          <v-card-title>
            Inventory Reconciliations
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
            ></v-text-field>
            <v-spacer></v-spacer>
            <v-autocomplete
              v-model="inventory_group"
              :items="inventory_groups"
              item-text="name"
              item-value="name"
              label="Inventory Group"
              v-if="user.id === 1"
            >
            </v-autocomplete>
            <v-spacer></v-spacer>
          </v-card-title>

          <v-data-table
            :headers="headers"
            :items="filteredInventory"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="hasPermission('inventory-recon-list')"
          >
            <template v-slot:item.status="{ item }">
              <v-chip
                :color="
                  item.status == 'unreconciled' ? 'red white--text' : 'success'
                "
              >
                {{ item.status.toUpperCase() }}
              </v-chip>
            </template>
            <template v-slot:item.actions="{ item }">
              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-icon
                    class="mr-2"
                    color="secondary"
                    small
                    @click="printPDF(item)"
                    v-bind="attrs"
                    v-on="on"
                  >
                    mdi-file-pdf
                  </v-icon>
                </template>
                <span>Generate PDF</span>
              </v-tooltip>
              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-icon
                    class="mr-2"
                    small
                    @click="viewBreakdown(item)"
                    v-bind="attrs"
                    v-on="on"
                  >
                    mdi-file-document
                  </v-icon>
                </template>
                <span>View Breakdown</span>
              </v-tooltip>
              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-icon
                    small
                    class="mr-2"
                    color="info"
                    @click="viewReconciliation(item)"
                    v-bind="attrs"
                    v-on="on"
                  >
                    mdi-eye
                  </v-icon>
                </template>
                <span>View Discrepancy</span>
              </v-tooltip>
              <v-tooltip bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-icon
                    small
                    color="red"
                    @click="showConfirmAlert(item)"
                    v-if="hasPermission('inventory-recon-delete')"
                    v-bind="attrs"
                    v-on="on"
                  >
                    mdi-delete
                  </v-icon>
                </template>
                <span>Delete</span>
              </v-tooltip>
            </template>
          </v-data-table>
        </v-card>
      </v-main>
      <ImportDialog 
        :api_route="api_route" 
        :dialog_import="dialog_import"
        :branches="branches"
        :databases="databases"
        :action="action"
        :inventory_group="inventory_group"
        @getData="getInventory"
        @closeImportDialog="closeImportDialog"
      />
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, requiredIf, maxLength, email } from "vuelidate/lib/validators";
import { mapState, mapGetters } from "vuex";
import ImportDialog from "../components/ImportDialog.vue";
import { jsPDF } from "jspdf";
import "jspdf-autotable";

export default {
  name: "InventoryReconciliation",
  components: {
    ImportDialog,
  },
  mixins: [validationMixin],

  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "branch.name" },
        { text: "Created By", value: "user.name" },
        { text: "Status", value: "status" },
        { text: "Date Created", value: "date_created" },
        { text: "Date Reconciled", value: "date_reconciled" },
        { text: "Actions", value: "actions", sortable: false, width: "150px"},
      ],
      disabled: false,
      dialog: false,
      inventory_reconciliations: [],
      branches: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Inventory Reconciliations",
          disabled: true,
          link: "/employee/list",
        },
      ],
      loading: true,
      search_branch: "",
      file: [],
      fileIsEmpty: false,
      fileIsInvalid: false,
      uploadDisabled: false,
      uploading: false,
      dialog_import: false,
      dialog_sync: false,
      dialog_error_list: false,
      errors_array: [],
      branch_id: "",
      inventory_groups: [{ name: "Admin-Branch" }, { name: "Audit-Branch" }],
      inventory_group: "Admin-Branch",
      products: [],
      branch: "",
      branch_code: "",
      date_reconciled: "",
      status: "",
      bm_oic: "",
      prepared_by: "",
      prepared_by_position: "",
      database: "",
      databases: [],
      api_route: "",
      action: "",
    };
  },

  methods: {
    getInventory() {
      this.loading = true;
      axios.get("/api/inventory_reconciliation/index").then(
        (response) => {
          let data = response.data;

          this.inventory_reconciliations = data.inventory_reconciliations;
          this.branches = data.branches;
          this.databases = data.databases;
          this.loading = false;

        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },
    async getInventoryReconciliation(inventory_recon_id) {
      await axios
        .get("/api/inventory_reconciliation/discrepancy/" + inventory_recon_id)
        .then(
          (response) => {
            let data = response.data;
            let reconciliation = data.reconciliation;
            this.products = data.products;
            this.branch = reconciliation.branch.name;
            this.branch_code = reconciliation.branch.code;
            this.date_reconciled = data.date_reconciled;
            this.status = reconciliation.status;
            this.bm_oic = reconciliation.branch.bm_oic;
            this.prepared_by = reconciliation.user.name;
            this.prepared_by_position = reconciliation.user.position
              ? reconciliation.user.position.name
              : "  ";
          },
          (error) => {
            this.isUnauthorized(error);
          }
        );
      await this.setPDFData();
    },

    openImportDialog(action) {
      this.action = action;
      this.api_route = action === "sync" ? "/api/inventory_reconciliation/sync" : "/api/inventory_reconciliation/import" ;
      this.dialog_import = true;
    },

    closeImportDialog() { 
      this.dialog_import = false;
    },

    showAlert(title, icon) {
      this.$swal({
        position: "center",
        icon: icon,
        title: title,
        showConfirmButton: false,
        timer: 2500,
      });
    },

    showErrorAlert(title, text) {
      this.$swal({
        position: "center",
        icon: "error",
        title: title,
        text: text,
        showConfirmButton: false,
        timer: 10000,
      });
    },

    deleteInventory(inventory_recon_id) {
      const data = { inventory_recon_id: inventory_recon_id };
      this.loading = true;
      axios.post("/api/inventory_reconciliation/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "inventory-delete" });
          }
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
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

          const inventory_recon_id = item.id;
          const index = this.inventory_reconciliations.indexOf(item);

          //Call delete Product function
          this.deleteInventory(inventory_recon_id);

          //Remove item from array inventories
          this.inventory_reconciliations.splice(index, 1);

          this.showAlert('Record has been deleted', 'success');

        }
      });
    },

    viewReconciliation(item) {
      this.$router.push({
        name: "inventory.reconciliation.discrepancy",
        params: { inventory_recon_id: item.id },
      });
    },

    viewBreakdown(item) {
      this.$router.push({
        name: "inventory.reconciliation.breakdown",
        params: { inventory_recon_id: item.id },
      });
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },

    printPDF(item) {
      this.getInventoryReconciliation(item.id);
    },

    setPDFData() {
      const doc = new jsPDF({
        orientation: "portrait",
        unit: "px",
        format: "letter",
      });

      let d = new Date();
      let months = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
      ];
      let thisMonth = months[d.getMonth()];
      let lastMonth = months[d.getMonth() - 1];
      let gMonth = d.getMonth() + 1;
      let gDate = d.getDate();
      let gFYear = d.getFullYear();

      let header = "ADDESSA CORPORATION";
      let invtymemo = "INVTY MEMO#";
      let date = "Date:";
      let to = "To:";
      let from = "From:";
  
      let invtymemo_value = this.branch_code + "-" + gFYear + "-" + gMonth;
      let date_value = thisMonth + " " + gDate + "," + gFYear;
      let bm_oic = this.bm_oic ? this.bm_oic : '';
      let to_position = "BM/OIC";
      let from_value = "Admin-Inventory Department";

      let beneath_table =
        "Please verify, reconcile and coordinate to admin for the reconciliation of the discrepancies within Ten (10) days upon the receipt of this Memo.";
      let beneath_from = "Physical Inventory Report for the month of";
      let beneath_from_value = lastMonth + " " + gFYear;
      let date_submitted = "Date Submitted:";
      let date_submitted_value = this.date_reconciled;

      let before_table =
        "We have reconciled your Physical Inventory Count Report versus SAP Report and we found out the following unreconciled items:";

      doc.setFontSize(7);
      doc.text(invtymemo_value, 80, 30);

      doc.setFontSize(7);
      doc.text(date_value, 80, 40);

      doc.setFontSize(7);
      doc.text(bm_oic, 80, 55);
      doc.text(to_position, 80, 60);

      doc.setFontSize(7);
      doc.text(from_value, 80, 75);

      doc.setFontSize(7);
      doc.text(before_table, 80, 118);

      doc.setFontSize(8);
      doc.setFont("", "bold");
      doc.text(header, 200, 16);

      doc.setFontSize(7);
      doc.setFont("", "bold");
      doc.text(invtymemo, 30, 30);

      doc.setFontSize(7);
      doc.setFont("", "bold");
      doc.text(date, 30, 40);

      doc.setFontSize(7);
      doc.setFont("", "bold");
      doc.text(to, 30, 55);

      doc.setFontSize(7);
      doc.setFont("", "bold");
      doc.text(from, 30, 75);

      doc.line(30, 80, 425, 80); // horizontal line

      doc.setFontSize(7);
      doc.setFont("", "bold");
      doc.text(beneath_from, 30, 88);

      doc.setFontSize(7);
      doc.setFont("", "bold");
      doc.text(beneath_from_value, 380, 88);

      doc.setFontSize(7);
      doc.setFont("", "bold");
      doc.text(date_submitted, 30, 97);

      doc.setFontSize(7);
      doc.setFont("", "bold");
      doc.text(date_submitted_value, 380, 97);

      let elem = document.getElementById("invty-recon-table", true);

      // let tbl = $('#invty-recon-table').clone();
      // tbl.find('thead tr:nth-child(1)').remove();
      // let res = doc.autoTableHtmlToJson(tbl.get(0));
      // let res = doc.autoTableHtmlToJson(elem);
      // console.log(res);
      let table_header = [
        "#",
        "Brand",
        "Model",
        "Product Category",
        "SAP Qty",
        "Branch Qty",
        "Diff.",
        "SAP Discrepancy",
        "Branch Discrepancy",
      ];

      let table_data = [
        [
          "#",
          "Brand",
          "Model",
          "Product Category",
          "SAP Qty",
          "Branch Qty",
          "Diff.",
          "SAP Discrepancy",
          "Branch Discrepancy",
        ],
      ];

      doc.autoTable(table_header, this.tableData, {
        startY: 130,
        theme: "grid",
        styles: {
          overflow: "linebreak",
          fontSize: 7,
          fillColor: [255, 255, 255],
          textColor: [0, 0, 0],
          lineColor: [0, 0, 0],
          lineWidth: 0.1,
        },
      });

      doc.setFontSize(7);
      doc.setFont("normal");
      doc.text(beneath_table, 80, doc.lastAutoTable.finalY + 15);

      let prepared_by = "Prepared by:";
      let prepared_by_value = this.prepared_by;
      let prepared_by_position = this.prepared_by_position;

      let verified_by = "Verified by:";
      let verified_by_value = "GERALD SUNIGA";
      let verified_by_position = "Inventory Recon Section Head";
      let verified_by_value_2 = "MARIEL QUITALEG";
      let verified_by_position_2 = "Inventory & Warehousing Manager";

      let noted_by = "Noted by:";
      let noted_by_value = "RAFAEL V. SORIANO";
      let noted_by_position = "General Manager";
      let noted_by_value_2 = "MS. SONIA DELA CRUZ";
      let noted_by_position_2 = "Vice President";

      // PREPARED BY
      doc.setFontSize(7);
      doc.setFont("normal");
      doc.text(prepared_by, 30, doc.autoTableEndPosY() + 40);

      doc.setFont("", "bold");
      doc.text(prepared_by_value, 80, doc.lastAutoTable.finalY + 40);

      doc.setFont("normal");
      doc.text(prepared_by_position, 80, doc.lastAutoTable.finalY + 45);

      // VERIFIED BY
      doc.setFontSize(7);
      doc.setFont("normal");
      doc.text(verified_by, 30, doc.lastAutoTable.finalY + 60);

      doc.setFont("", "bold");
      doc.text(verified_by_value, 80, doc.lastAutoTable.finalY + 60);

      doc.setFont("normal");
      doc.text(verified_by_position, 80, doc.lastAutoTable.finalY + 65);

      doc.setFont("", "bold");
      doc.text(verified_by_value_2, 180, doc.lastAutoTable.finalY + 60);

      doc.setFont("normal");
      doc.text(verified_by_position_2, 180, doc.lastAutoTable.finalY + 65);

      // VERIFIED BY
      doc.setFontSize(7);
      doc.setFont("normal");
      doc.text(noted_by, 30, doc.lastAutoTable.finalY + 80);

      doc.setFont("", "bold");
      doc.text(noted_by_value, 80, doc.lastAutoTable.finalY + 80);

      doc.setFont("normal");
      doc.text(noted_by_position, 80, doc.lastAutoTable.finalY + 85);

      doc.setFont("", "bold");
      doc.text(noted_by_value_2, 180, doc.lastAutoTable.finalY + 80);

      doc.setFont("normal");
      doc.text(noted_by_position_2, 180, doc.lastAutoTable.finalY + 85);

      doc.output("dataurlnewwindow");

      doc.save("inventory.pdf");
    },
    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;

        if (
          action == "employee-create" ||
          action == "employee-edit" ||
          action == "employee-delete" ||
          action == "employee-import"
        ) {
          this.getInventory();
        }
      };
    },
  },
  computed: {
    filteredInventory() {
      let inventory = [];

      if (this.user.id !== 1) {
        // if user has role Audit Admin
        if (this.hasRole('Audit Admin')) {
          this.inventory_group = "Audit-Branch";
        }
        // if user has role Inventory Admin
        else if (this.hasRole('Inventory Admin')) {
          this.inventory_group = "Admin-Branch";
        }
      }

      this.inventory_reconciliations.forEach((value, index) => {
        if (value.inventory_group === this.inventory_group) {
          inventory.push(value);
        }
      });

      return inventory;
    },

    tableData() {
      let table_data = [];
      this.products.forEach((value, index) => {
        table_data.push([
          index + 1,
          value.brand,
          value.model,
          value.product_category,
          value.sap_qty,
          value.physical_qty,
          value.qty_diff,
          value.sap_discrepancy,
          value.physical_discrepancy,
        ]);
      });

      return table_data;
    },
    btnLabel(){
      return this.importIsClicked ? 'Upload' : 'Sync';
    },
    loadingLabel(){
      return this.importIsClicked ? 'Uploading...' : 'Syncing...';
    },
    dialogHeaderTitle(){
      return this.importIsClicked ? 'Import Excel Data From SAP' : 'Sync Data From SAP';
    },
    ...mapState("auth", ["user"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission","hasAnyPermission"]),
  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");

    this.getInventory();
    // this.websocket();
  },
};
</script>