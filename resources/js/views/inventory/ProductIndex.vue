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
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="userPermissions.product_reconcile"
                >
                  <v-list-item-title>
                    <v-btn
                      class="ma-2"
                      color="primary"
                      width="120px"
                      small
                      @click="getUnreconciled()"
                    >
                      <v-icon class="mr-1" small> mdi-import </v-icon>
                      Reconcile
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="userPermissions.product_export"
                >
                  <v-list-item-title>
                    <v-btn
                      class="ma-2"
                      color="success"
                      width="120px"
                      small
                      @click="exportData()"
                    >
                      <v-icon class="mr-1" small> mdi-microsoft-excel </v-icon>
                      Export
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="userPermissions.product_clear_list"
                >
                  <v-list-item-title>
                    <v-btn
                      class="ma-2"
                      color="error"
                      width="120px"
                      small
                      @click="clearList()"
                      ><v-icon class="mr-1" small> mdi-delete </v-icon>clear
                      list</v-btn
                    >
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
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
            <v-autocomplete
              v-model="search_branch"
              :items="branches"
              item-text="name"
              item-value="id"
              label="Branch"
              v-if="user.id === 1 || userPermissions.product_list_all"
            >
            </v-autocomplete>
            <template>
              <v-toolbar flat>
                <v-spacer></v-spacer>
                <v-dialog v-model="dialog" max-width="500px" persistent>
                  <v-card>
                    <v-card-title>
                      <span class="headline">{{ formTitle }}</span>
                    </v-card-title>
                    <v-divider></v-divider>
                    <v-card-text>
                      <v-container>
                        <v-row>
                          <v-col class="mt-0 mb-0 pt-0 pb-0">
                            <v-autocomplete
                              v-model="editedItem.brand_id"
                              :items="brands"
                              item-text="name"
                              item-value="id"
                              label="Brand"
                              required
                              :error-messages="brandErrors"
                              @input="
                                $v.editedItem.brand_id.$touch() +
                                  (serialExists = false)
                              "
                              @blur="$v.editedItem.brand_id.$touch()"
                            >
                            </v-autocomplete>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="mt-0 mb-0 pt-0 pb-0">
                            <v-text-field
                              name="model"
                              label="Model"
                              v-model="editedItem.model"
                              required
                              :error-messages="modelErrors"
                              @input="
                                $v.editedItem.model.$touch() +
                                  (serialExists = false)
                              "
                              @blur="$v.editedItem.model.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="mt-0 mb-0 pt-0 pb-0">
                            <v-autocomplete
                              v-model="editedItem.product_category_id"
                              :items="product_categories"
                              item-text="name"
                              item-value="id"
                              label="Product Category"
                              required
                              :error-messages="product_categoryErrors"
                              @input="
                                $v.editedItem.product_category_id.$touch() +
                                  (serialExists = false) +
                                  selectedProductCategory()
                              "
                              @blur="$v.editedItem.product_category_id.$touch()"
                            >
                            </v-autocomplete>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="mt-0 mb-0 pt-0 pb-0">
                            <v-text-field
                              name="serial"
                              label="Serial"
                              v-model="editedItem.serial"
                              readonly
                              required
                              :error-messages="serialErrors"
                              @input="$v.editedItem.serial.$touch()"
                              @blur="$v.editedItem.serial.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="mt-0 mb-0 pt-0 pb-0">
                            <v-autocomplete
                              v-model="editedItem.branch_id"
                              :items="branches"
                              item-text="name"
                              item-value="id"
                              label="Branch"
                              required
                              :error-messages="branchErrors"
                              @input="$v.editedItem.branch_id.$touch()"
                              @blur="$v.editedItem.branch_id.$touch()"
                              v-if="user.id === 1"
                            >
                            </v-autocomplete>
                          </v-col>
                        </v-row>
                      </v-container>
                    </v-card-text>

                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="#E0E0E0" @click="close" class="mb-4">
                        Cancel
                      </v-btn>
                      <v-btn
                        color="primary"
                        @click="save"
                        class="mb-4 mr-4"
                        :disabled="disabled"
                      >
                        Save
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>

                <v-dialog
                  v-model="dialog_unreconciled"
                  max-width="1000px"
                  persistent
                >
                  <v-card>
                    <v-card-title>
                      <span class="headline">Unreconciled Inventories</span>
                      <v-spacer></v-spacer>
                      <v-text-field
                        v-model="search_unreconciled"
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
                    </v-card-title>
                    <v-divider></v-divider>
                    <v-card-text>
                      <v-container>
                        <v-row v-if="user.id === 1"> </v-row>
                        <v-row>
                          <v-col>
                            <v-data-table
                              :headers="unreconciled_headers"
                              :items="filteredUnreconciled"
                              :search="search_unreconciled"
                              :loading="loading_unreconciled"
                              loading-text="Loading... Please wait"
                            >
                              <template v-slot:item.status="{ item }">
                                <v-chip
                                  :color="
                                    item.status == 'unreconciled'
                                      ? 'red white--text'
                                      : 'success'
                                  "
                                >
                                  {{ item.status.toUpperCase() }}
                                </v-chip>
                              </template>
                              <template v-slot:item.actions="{ item }">
                                <v-btn
                                  x-small
                                  class="mr-2"
                                  color="primary"
                                  @click="reconcileProducts(item)"
                                >
                                  <v-icon x-small class="mr-1">
                                    mdi-file
                                  </v-icon>
                                  Reconcile
                                </v-btn>
                              </template>
                            </v-data-table>
                          </v-col>
                        </v-row>
                      </v-container>
                    </v-card-text>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn
                        color="#E0E0E0"
                        @click="
                          (dialog_unreconciled = false) + (loading = false)
                        "
                        class="mb-4 mr-4"
                      >
                        Cancel
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
              </v-toolbar>
            </template>
          </v-card-title>
          <v-data-table
            :headers="tableHeaders"
            :items="filteredProducts"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="userPermissions.product_list"
          >
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editProduct(item)"
                v-if="userPermissions.product_edit"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="userPermissions.product_delete"
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
        { text: "Brand", value: "brand.name" },
        { text: "Model", value: "model" },
        { text: "Product Category", value: "product_category.name" },
        { text: "Serial", value: "serial" },
        { text: "Branch", value: "branch.name" },
        { text: "Date Created", value: "date_created" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      unreconciled_headers: [
        { text: "Branch", value: "branch.name" },
        { text: "Date Created", value: "date_created" },
        { text: "Status", value: "status" },
        { text: "Actions", value: "actions", sortable: false, width: "100px" },
      ],
      disabled: false,
      dialog: false,
      dialog_unreconciled: false,
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
        },
      ],
      loading: true,
      loading_unreconciled: true,
      user: "",
      search_branch: "",
      json_fields: {
        BRAND: "brand.name",
        MODEL: "model",
        SERIAL: "serial",
        QUANTITY: " ",
      },
      serialExists: false,
      inventory_groups: [{ name: "Admin-Branch" }, { name: "Audit-Branch" }],
      inventory_group: "Admin-Branch",
      unreconciled_list: [],
      search_unreconciled: "",
    };
  },

  methods: {
    getProduct() {
      this.loading = true;
      axios.get("/api/product/index").then(
        (response) => {
          let data = response.data;

          this.user = data.user;
          this.products = data.products;
          this.brands = data.brands;
          this.branches = data.branches;
          this.branches.unshift({ id: 0, name: "ALL" });
          this.product_categories = data.product_categories;
          this.editedItem.branch_id = this.user.branch_id;
          this.search_branch = this.user.branch_id;
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

      this.product_categories.forEach((value) => {
        if (this.editedItem.product_category_id == value.id) {
          product_category = value;
        }
      });

      this.editedItem.product_category = product_category;
    },

    getUnreconciled() {
      if (this.filteredProducts.length) {
        // if Dropdown Branch value is 'ALL'
        if (this.search_branch == 0) {
          this.$swal({
            position: "center",
            icon: "warning",
            title: "Please select specific branch!",
            showConfirmButton: false,
            timer: 4000,
          });
        } else {
          this.loading_unreconciled = true;
          this.dialog_unreconciled = true;

          let data = {
            branch_id: this.search_branch,
            inventory_group: this.inventory_group,
          };

          axios
            .post("/api/inventory_reconciliation/unreconcile/list", data)
            .then(
              (response) => {
                this.unreconciled_list = response.data.unreconciled_list;
                this.loading_unreconciled = false;
              },
              (error) => {
                console.log(error);
              }
            );
        }
      } else {
        this.$swal({
          position: "center",
          icon: "warning",
          title: "Nothing to reconcile",
          showConfirmButton: false,
          timer: 2500,
        });
      }
    },
    reconcileProducts(item) {
      this.$swal({
        title: "Reconcile Products",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "primary",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Proceed",
      }).then((result) => {
        // <--

        if (result.value) {
          // <-- if confirmed

          let data = {
            inventory_recon_id: item.id,
            products: this.filteredProducts,
            inventory_group: this.inventory_group,
          };

          axios.post("api/inventory_reconciliation/reconcile", data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "product-reconcile" });

                let index = this.unreconciled_list.indexOf(item);
                this.unreconciled_list.splice(index, 1);

                this.$swal({
                  position: "center",
                  icon: "success",
                  title: "Inventory has been reconciled",
                  showConfirmButton: false,
                  timer: 2500,
                });
              } else if (response.data.duplicate) {
                this.$swal({
                  position: "center",
                  icon: "warning",
                  title: "Products already exist",
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
    filteredUnreconciled() {
      let unreconciled = [];

      if (this.user.id !== 1) {
        // if user has role Audit Admin
        if (this.userRoles.audit_admin) {
          this.inventory_group = "Audit-Branch";
        }
        // if user has role Inventory Admin
        else if (this.userRoles.inventory_admin) {
          this.inventory_group = "Admin-Branch";
        }
      }

      this.unreconciled_list.forEach((value, index) => {
        if (value.inventory_group === this.inventory_group) {
          unreconciled.push(value);
        }
      });

      return unreconciled;
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
  async mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    await this.getProduct();
    this.$barcodeScanner.init(this.onBarcodeScanned);
    // this.websocket();
  },
};
</script>