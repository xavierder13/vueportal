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
        <MenuActions
          :canExport="true"
          :canPrintPDF="true"
          @export="exportData"
          @printPDF="printPDF"
        />
        <v-card>
          <v-card-title>
            Inventory Reconciliation Breakdown - {{ branch }}
            <v-chip
              :color="
                status == 'unreconciled' ? 'red white--text' : 'success'
              "
              class="ml-4"
            >
              {{ status.toUpperCase() }}
            </v-chip>
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
            :search="search"
            :items="products"
            :loading="loading"
            loading-text="Loading... Please wait"
            id="invty-recon-table"
          >
            <template v-slot:item.row="{ item, index }">
              {{ index + 1 }}
            </template>
          </v-data-table>
        </v-card>
      </v-main>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { mapState, mapGetters } from "vuex";
import { jsPDF } from "jspdf";
import "jspdf-autotable";
import MenuActions from '../../components/MenuActions.vue';

export default {
  name: "InventoryBreakdown",
  components: {
    MenuActions,
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "#", value: "row" },
        { text: "Brand", value: "brand" },
        { text: "Model", value: "model" },
        { text: "Category", value: "product_category" },
        { text: "SAP Serial", value: "sap_serial" },
        { text: "Branch Serial", value: "physical_serial" },
      ],
      disabled: false,
      dialog: false,
      products: [],
      brands: [],
      branches: [],
      product_categories: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: " INVENTORY RECONCILIATIONS ",
          disabled: false,
          link: "/inventory/reconciliation",
        },
        {
          text: "Breakdown",
          disabled: true,
        },
      ],
      loading: true,
      user: "",
      search_branch: "",
      json_fields: {
        Brand: "brand",
        Model: "model",
        "Category": "product_category",
        "SAP Serial": "sap_serial",
        "Branch Serial": "physical_serial",
      },
      serialExists: false,
      branch: "",
      branch_code: "",
      date_reconciled: "",
      status: "",
      bm_oic: "",
      prepared_by: "",
      prepared_by_position: "",
    };
  },

  methods: {
    getProduct() {
      this.loading = true;
      let inventory_recon_id = this.$route.params.inventory_recon_id;
      axios
        .get("/api/inventory_reconciliation/breakdown/" + inventory_recon_id)
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
            this.prepared_by_position = reconciliation.user.position ? reconciliation.user.position.name : '  ';
            this.loading = false;
            
          },
          (error) => {
            this.isUnauthorized(error);
          }
        );
    },

    deleteInventory() {
      if (this.products.length) {
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

            const data = {
              inventory_recon_id: this.$route.params.inventory_recon_id,
            };
            this.loading = true;
            axios.post("/api/inventory_reconciliation/delete", data).then(
              (response) => {
                if (response.data.success) {

                  this.showAlert(response.data.success, "success");

                  this.$router.push({ name: "inventory.reconciliation" });
                }
                this.loading = false;
              },
              (error) => {
                this.isUnauthorized(error);
              }
            );
          }
        });
      } else {
        this.showAlert("No record found", "warning")
      }
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

    close() {
      this.dialog = false;
      this.clear();
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedItem.branch_id = this.user.branch_id;
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },

    printPDF() {
      if (this.products.length) {
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
        doc.setFont("","bold");
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
          "SAP Serial",
          "Branch Serial",
        ];
        console.log(this.tableData);
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

        // doc.output("dataurlnewwindow");

        doc.save("ReconciliationBreakdown.pdf");
      } else {

        this.showAlert("No record found", "warning")
      }
    },
    exportData() {
   
      if (this.products.length) {
        let inventory_recon_id = this.$route.params.inventory_recon_id;
        axios.get('/api/inventory_reconciliation/export_breakdown/' + inventory_recon_id, { responseType: 'arraybuffer'})
          .then((response) => {
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement('a');
              fileLink.href = fileURL;
              fileLink.setAttribute('download', 'InventoryBreakdown.xls');
              document.body.appendChild(fileLink);
              fileLink.click();
          }, (error) => {
            console.log(error);
          }
        );
      } else {
        this.showAlert("No record found", "warning")
      }
    },

    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;

        if (
          action == "product-create" ||
          action == "product-edit" ||
          action == "product-delete"
        ) {
          this.getProduct();
        }
      };
    },
  },
  computed: {
    tableData() {
      let table_data = [];
      this.products.forEach((value, index) => {
        console.log(value);
        table_data.push([
          index + 1,
          value.brand,
          value.model,
          value.product_category,
          value.sap_serial,
          value.physical_serial,
        ]);
      });

      return table_data;
    },
    formattedProducts(){

      // add apostrophe (') special character if serial number is numeric and character length is greater than 15 digits
      // excel numeric format cell only reads maximum of 15 digits

      let products = [];
      this.products.forEach(value => {
        products.push({
          brand: value.brand,
          model: value.model,
          physical_serial: !isNaN(value.physical_serial) && value.physical_serial.length > 15 ? "'" + value.physical_serial : value.physical_serial,
          product_category: value.product_category,
          sap_serial: !isNaN(value.sap_serial) && value.sap_serial.length > 15 ? "'" + value.sap_serial : value.sap_serial,
        });
       
      });

      
      return products;
    },
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getProduct();
    // this.websocket();
  },
};
</script>