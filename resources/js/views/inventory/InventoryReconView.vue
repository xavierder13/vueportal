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
                <v-list-item
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
                </v-list-item>
              </v-list>
            </v-menu>
          </div>
        </div>
        <v-card>
          <v-card-title>
            Inventory Reconciliation - {{ branch }}
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
            :loading="loading"
            loading-text="Loading... Please wait"
          >
            <template v-slot:item.row="{ item, index }">
              {{ index + 1 }}
            </template>
            <template v-slot:item.qty_diff="{ item, index }">
              <v-chip
                x-small
                :color="item.qty_diff > 0 ? 'success' : 'red white--text'"
                v-if="item.qty_diff != 0"
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
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState } from "vuex";

export default {
  mixins: [validationMixin],

  validations: {
    editedItem: {
      branch_id: { required },
      brand_id: { required },
      model: { required },
      product_category_id: { required },
      serial: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "#", value: "row" },
        { text: "Brand", value: "brand" },
        { text: "Model", value: "model" },
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
      editedIndex: -1,
      editedItem: {
        branch_id: "",
        brand: "",
        brand_id: "",
        model: "",
        serial: "",
      },
      defaultItem: {
        branch_id: "",
        brand: "",
        brand_id: "",
        model: "",
        serial: "",
      },
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
          text: "View",
          disabled: true,
        },
      ],
      loading: true,
      user: "",
      search_branch: "",
      json_fields: {
        "Brand": "brand",
        "Model": "model",
        "SAP Qty": "sap_qty",
        "Branch Qty": "physical_qty",
        "Diff.": "qty_diff",
        "SAP Discrepancy": "sap_discrepancy",
        "Branch Discrepancy": "physical_discrepancy"
      },
      serialExists: false,
      branch: "",
    };
  },

  methods: {
    getProduct() {
      this.loading = true;
      let inventory_recon_id = this.$route.params.inventory_recon_id;
      axios
        .get("/api/inventory_reconciliation/view/" + inventory_recon_id)
        .then(
          (response) => {
            this.products = response.data.products;
            this.branch = response.data.branch;
            this.loading = false;
          },
          (error) => {
            this.isUnauthorized(error);
          }
        );
    },

    editProduct(item) {
      this.editedIndex = this.products.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
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
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
  },
  created() {
    // Add barcode scan listener and pass the callback function
    this.$barcodeScanner.init(this.onBarcodeScanned);
  },
  destroyed() {
    // Remove listener when component is destroyed
    this.$barcodeScanner.destroy();
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getProduct();
    this.$barcodeScanner.init(this.onBarcodeScanned);
    // this.websocket();
  },
};
</script>