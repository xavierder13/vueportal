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
              class="ml-4"
              small
              @click="exportData()"
              v-if="userPermissions.product_export"
            >
              <v-icon class="mr-1" small> mdi-microsoft-excel </v-icon>
              Export
            </v-btn>
            <!-- <export-excel
              class="btn btn-default pa-0 ma-0"
              :data="products"
              :fields="json_fields"
              worksheet="My Worksheet"
              name="products.xls"
              v-if="userPermissions.product_export"
            >
              <v-btn
                color="success"
                small
                v-if="userPermissions.product_export"
                @click="alert()"
              >
                <v-icon class="mr-1" small> mdi-microsoft-excel </v-icon>
                export
              </v-btn>
            </export-excel> -->
          </div>
          <div>
            <v-divider
              vertical
              class="ml-2 mr-2"
              v-if="userPermissions.product_clear_list"
            ></v-divider>
          </div>
          <div>
            <v-btn
              color="error"
              small
              @click="clearList()"
              v-if="userPermissions.product_clear_list"
              ><v-icon class="mr-1" small> mdi-delete </v-icon>clear list</v-btn
            >
          </div>
        </div>
        <v-card>
          <v-card-title>
            Product Lists
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
            :headers="tableHeaders"
            :items="products"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
          >
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
        { text: "Brand", value: "brand" },
        { text: "Model", value: "model" },
        { text: "Product Category", value: "product_category" },
        { text: "Serial", value: "serial" },
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
          text: "Product Lists",
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
        BRAND: "brand",
        MODEL: "model",
        SERIAL: "serial",
        PRODUCT_CATEGORY: "product_category",
        QUANTITY: " ",
      },
      serialExists: false,
    };
  },

  methods: {
    getProduct() {
      this.loading = true;
      let inventory_recon_id = this.$route.params.inventory_recon_id;
      axios.get("/api/inventory_reconciliation/view/" + inventory_recon_id).then(
        (response) => {
       
          this.products = response.data.inventory_reconciliation;
             console.log(this.products );
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

    deleteProduct(product_id) {
      const data = { product_id: product_id };
      this.loading = true;
      axios.post("/api/product/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "product-delete" });
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

          const product_id = item.id;
          const index = this.products.indexOf(item);

          //Call delete Product function
          this.deleteProduct(product_id);

          //Remove item from array products
          this.products.splice(index, 1);

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

    close() {
      this.dialog = false;
      this.clear();
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    save() {
      this.$v.$touch();

      if (!this.$v.$error) {
        this.disabled = true;

        if (this.editedIndex > -1) {
          const data = this.editedItem;
          const product_id = this.editedItem.id;

          axios.post("/api/product/update/" + product_id, data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "product-edit" });

                Object.assign(this.products[this.editedIndex], this.editedItem);
 
                this.showAlert();
                this.close();
              } else if (response.data.existing_products) {
                this.serialExists = true;
              }

              this.disabled = false;
            },
            (error) => {
              this.isUnauthorized(error);
              this.disabled = false;
            }
          );
        } else {
          const data = this.editedItem;

          axios.post("/api/product/store", data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "product-create" });

                this.showAlert();
                this.close();

                //push recently added data from database
                this.products.push(response.data.product);
              } else if (response.data.existing_products) {
                this.serialExists = true;
              }
              this.disabled = false;
            },
            (error) => {
              this.isUnauthorized(error);
              this.disabled = false;
            }
          );
        }
      }
    },

    clearList() {
      if (this.filteredProducts.length) {
        this.$swal({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#d33",
          cancelButtonColor: "#6c757d",
          confirmButtonText: "Clear List!",
        }).then((result) => {
          // <--

          if (result.value) {
            // <-- if confirmed

            let data = { branch_id: this.search_branch, clear_list: true };

            axios.post("api/product/delete", data).then(
              (response) => {
                if (response.data.success) {
                  // send data to Sockot.IO Server
                  // this.$socket.emit("sendData", { action: "product-create" });

                  let products = this.products;

                  // clear products array
                  this.products = [];

                  products.forEach((value, index) => {
                    // push products to array where except deleted data
                    if (value.branch_id != this.search_branch) {
                      this.products.push(value);
                    } else if (this.search_branch === 0) {
                      this.products = [];
                    }
                  });

                  this.$swal({
                    position: "center",
                    icon: "success",
                    title: "Record has been cleared",
                    showConfirmButton: false,
                    timer: 2500,
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
    exportData() {
      if (this.filteredProducts.length) {
        window.open(
          location.origin + "/api/product/export/" + this.search_branch,
          "_blank"
        );
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
    selectedProductCategory() {
      let product_category = {};

      this.product_categories.forEach(value => {
        if(this.editedItem.product_category_id == value.id)
        {
          product_category = value;
        }
      });

      this.editedItem.product_category = product_category;
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
    // Create callback function to receive barcode when the scanner is already done
    onBarcodeScanned(barcode) {
      // console.log(barcode);
      this.serialExists = false;
      this.editedItem.serial = barcode;
      // do something...
    },
    // Reset to the last barcode before hitting enter (whatever anything in the input box)
    resetBarcode() {
      let barcode = this.$barcodeScanner.getPreviousCode();
      // do something...
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Product" : "Edit Product";
    },
    brandErrors() {
      const errors = [];
      if (!this.$v.editedItem.brand_id.$dirty) return errors;
      !this.$v.editedItem.brand_id.required &&
        errors.push("Brand is required.");
      return errors;
    },
    modelErrors() {
      const errors = [];
      if (!this.$v.editedItem.model.$dirty) return errors;
      !this.$v.editedItem.model.required && errors.push("Model is required.");
      return errors;
    },
    product_categoryErrors() {
      const errors = [];
      if (!this.$v.editedItem.product_category_id.$dirty) return errors;
      !this.$v.editedItem.product_category_id.required &&
        errors.push("Product Category is required.");
      return errors;
    },
    serialErrors() {
      const errors = [];
      if (!this.$v.editedItem.serial.$dirty) return errors;
      !this.$v.editedItem.serial.required && errors.push("Serial is required.");
      if (this.serialExists) {
        errors.push("Serial exists");
      }
      return errors;
    },
    branchErrors() {
      const errors = [];
      if (!this.$v.editedItem.branch_id.$dirty) return errors;
      !this.$v.editedItem.branch_id.required &&
        errors.push("Branch is required.");
      return errors;
    },
    filteredProducts() {
      let products = [];

      this.products.forEach((value) => {
        if (this.search_branch === 0) {
          products.push(value);
        } else if (value.branch_id === this.search_branch) {
          products.push(value);
        }
      });

      return products;
    },
    tableHeaders() {
      let headers = [];

      this.headers.forEach((value) => {
        headers.push(value);
      });

      // remove Actions column if user is not permitted
      if (
        !this.userPermissions.product_edit &&
        !this.userPermissions.product_delete
      ) {
        headers.splice(5, 1);
      }

      return headers;
    },
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