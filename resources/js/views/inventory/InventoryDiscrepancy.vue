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
            <v-menu offset-y>
              <template v-slot:activator="{ on, attrs }">
                <v-btn small v-bind="attrs" v-on="on" color="primary"> 
                  Actions
                  <v-icon small> mdi-menu-down </v-icon>
                </v-btn>
              </template>
              <v-list class="pa-1">
                <v-list-item class="ma-0 pa-0" style="min-height: 25px">
                  <v-list-item-title>
                    <v-btn
                      class="ma-2"
                      color="secondary"
                      small
                      @click="printPDF()"
                    >
                      <v-icon class="mr-1" small> mdi-file-pdf </v-icon>
                      Print PDF
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item class="ma-0 pa-0" style="min-height: 25px">
                  <v-list-item-title>
                    <!-- <v-btn
                      class="ma-2"
                      color="success"
                      width="120px"
                      small
                      @click="exportData()"
                    >
                      <v-icon class="mr-1" small> mdi-microsoft-excel </v-icon>
                      Export
                    </v-btn> -->

                    <export-excel
                      :data="products"
                      :fields="json_fields"
                      type="xls"
                      :name="branch + '_Reconciliations.xls'"
                    >
                      <v-btn class="ma-2" color="success" width="120px" small>
                        <v-icon class="mr-1" small>
                          mdi-microsoft-excel
                        </v-icon>
                        Export
                      </v-btn>
                    </export-excel>
                  </v-list-item-title>
                </v-list-item>
                <!-- <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="userPermissions.inventory_recon_delete"
                >
                  <v-list-item-title>
                    <v-btn
                      class="ma-2"
                      color="error"
                      width="120px"
                      small
                      @click="deleteInventory()"
                      ><v-icon class="mr-1" small> mdi-delete </v-icon>delete
                    </v-btn>
                  </v-list-item-title>
                </v-list-item> -->
              </v-list>
            </v-menu>
          </div>
        </div>
        <v-card>
          <v-card-title>
            Inventory Reconciliation - {{ branch }}
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
            :items="products"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            id="invty-recon-table"
          >
            <template v-slot:item.row="{ item, index }">
              {{ index + 1 }}
            </template>
            <template v-slot:item.qty_diff="{ item, index }">
              <v-chip
                x-small
                :color="item.qty_diff === 0 ? '': item.qty_diff > 0 ? 'success' : 'red white--text'"
                >{{ item.qty_diff }}</v-chip
              >
            </template>
            <template v-slot:item.sap_discrepancy="{ item, index }">
              <span class="text-success"> {{ item.sap_discrepancy }} </span>
            </template>
            <template v-slot:item.physical_discrepancy="{ item, index }">
              <span class="text-danger"> {{ item.physical_discrepancy }} </span>
            </template>
          </v-data-table>
          <!-- <v-simple-table>
            <thead>
              <tr>
                <th>#</th>
                <th>Brand</th>
                <th>Model</th>
                <th>SAP Qty</th>
                <th>Branch Qty</th>
                <th>Diff.</th>
                <th>SAP Serial Discrepancy</th>
                <th>Branch Serial Discrepancy</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in products">
                <td>{{ index + 1 }}</td>
                <td>{{ item.brand }}</td>
                <td>{{ item.model }}</td>
                <td>{{ item.sap_qty }}</td>
                <td>{{ item.physical_qty }}</td>
                <td>
                  <v-chip
                    x-small
                    :color="item.qty_diff > 0 ? 'success' : 'red white--text'"
                    v-if="item.qty_diff != 0"
                    >{{ item.qty_diff }}</v-chip
                  >
                </td>
                <td class="text-success">{{ item.sap_discrepancy }}</td>
                <td class="text-danger">{{ item.physical_discrepancy }}</td>
              </tr>
            </tbody>
          </v-simple-table> -->
        </v-card>
      </v-main>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { mapState } from "vuex";
import { jsPDF } from "jspdf";
import "jspdf-autotable";

export default {
  data() {
    return {
      search: "",
      headers: [
        { text: "#", value: "row" },
        { text: "Brand", value: "brand" },
        { text: "Model", value: "model" },
        { text: "Product Category", value: "product_category" },
        { text: "SAP Qty", value: "sap_qty" },
        { text: "Branch Qty", value: "physical_qty" },
        { text: "Diff.", value: "qty_diff" },
        { text: "SAP Discrepancy", value: "sap_discrepancy" },
        { text: "Branch Discrepancy", value: "physical_discrepancy" },
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
          text: "Discrepancy",
          disabled: true,
        },
      ],
      loading: true,
      user: "",
      search_branch: "",
      json_fields: {
        Brand: "brand",
        Model: "model",
        "Product Category": "product_category",
        "SAP Qty": "sap_qty",
        "Branch Qty": "physical_qty",
        "Diff.": "qty_diff",
        "SAP Discrepancy": "sap_discrepancy",
        "Branch Discrepancy": "physical_discrepancy",
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
            this.prepared_by_position = reconciliation.user.position ? reconciliation.user.position.name : ' ';
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
                  this.$swal({
                    position: "center",
                    icon: "success",
                    title: "Record has been deleted",
                    showConfirmButton: false,
                    timer: 2500,
                  });

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
        this.$swal({
          position: "center",
          icon: "warning",
          title: "No record found",
          showConfirmButton: false,
          timer: 2500,
        });
      }
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
        let to_value =
          "{{ $branch->bm_oic ? strtoupper($branch->bm_oic) : 'NONE' }}";
        let to_position = "BM/OIC";
        let from_value = "Admin-Inventory Department";

        let beneath_table =
          "Please verify, reconcile and coordinate to admin for the reconciliation of the discrepancies within Two (2) days upon the receipt of this Memo.";
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
        doc.text(this.bm_oic, 80, 55);
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
        let verified_by_position = "Inventory Section Head";
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
      } else {
        this.$swal({
          position: "center",
          icon: "warning",
          title: "No record found",
          showConfirmButton: false,
          timer: 2500,
        });
      }
    },
    exportData() {
      if (this.products.length) {
      } else {
        this.$swal({
          position: "center",
          icon: "warning",
          title: "No record found",
          showConfirmButton: false,
          timer: 2500,
        });
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
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getProduct();
    // this.websocket();
  },
};
</script>