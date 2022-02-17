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
            Product Model Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="userPermissions.product_model_list"
            ></v-text-field>
            <template>
              <v-toolbar flat>
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  fab
                  dark
                  class="mb-2"
                  @click="clear() + (dialog = true)"
                  v-if="userPermissions.product_model_create"
                >
                  <v-icon>mdi-plus</v-icon>
                </v-btn>
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
                            <v-text-field
                              name="product_model"
                              v-model="editedItem.name"
                              label="Product Model"
                              required
                              :error-messages="
                                product_modelErrors + product_modelError.name
                              "
                              @input="
                                $v.editedItem.name.$touch() +
                                  (product_modelError.name = [])
                              "
                              @blur="$v.editedItem.name.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col cols="2" class="mt-0 mb-0 pt-0 pb-0">
                            <v-switch
                              v-model="switch1"
                              :label="activeStatus"
                            ></v-switch>
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
              </v-toolbar>
            </template>
          </v-card-title>

          <v-data-table
            :headers="headers"
            :items="product_models.data"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            :page.sync="page"
            :footer-props="footerProps"
            @page-count="pageCount = $event"
            :items-per-page="itemsPerPage"
            @update:items-per-page="getItemPerPage"
            v-if="userPermissions.product_model_list"
            show-first-last-page
          >
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editProductModel(item)"
                v-if="userPermissions.product_model_edit"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="userPermissions.product_model_delete"
              >
                mdi-delete
              </v-icon>
            </template>
            <template v-slot:footer.page-text>
              <!-- <v-btn color="primary" dark class="ma-2"> Button </v-btn> -->
              {{
                product_models.from
                  ? product_models.from +
                    " - " +
                    product_models.to +
                    " of " +
                    product_models.total
                  : ""
              }}
              <v-btn icon class="ml-4" @click="firstPage()" :disabled="firstBtnDisable"
                ><v-icon> mdi-chevron-double-left </v-icon></v-btn
              >
              <v-btn icon class="mr-2" @click="prevPage()" :disabled="prevBtnDisable"
                ><v-icon> mdi-chevron-left </v-icon></v-btn
              >
              <v-btn icon @click="nextPage()" :disabled="lastBtnDisable"><v-icon> mdi-chevron-right </v-icon></v-btn>
              <v-btn icon @click="lastPage()" :disabled="lastBtnDisable"
                ><v-icon> mdi-chevron-double-right </v-icon></v-btn
              >
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
      name: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Product Model", value: "name" },
        { text: "Active", value: "active" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      page: 1,
      pageCount: 0,
      itemsPerPage: 10,
      numRowsPerPage: 0,
      footerProps: {
        "items-per-page-options": [5, 10, 15, 30, 50, 100],
        pagination: {},
        showFirstLastPage: true,
        firstIcon: null,
        lastIcon: null,
        prevIcon: null,
        nextIcon: null,
      },
      switch1: true,
      disabled: false,
      dialog: false,
      product_models: {},
      editedIndex: -1,
      editedItem: {
        name: "",
        active: "Y",
      },
      defaultItem: {
        name: "",
        active: "Y",
      },
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Product Model Lists",
          disabled: true,
        },
      ],
      loading: true,
      product_modelError: {
        name: [],
      },
      last_page: 0,
      prevBtnDisable: true,
      nextBtnDisable: true,
      firstBtnDisable: true,
      lastBtnDisable: false,
      modelTotalCount : 0,
    };
  },

  methods: {
    getProductModel() {
      this.loading = true;
      axios.get("/api/product_model/index").then(
        (response) => {
          axios.get(response.data.product_models.path + "?page=" + this.page).then(
            (response) => {
              console.log(response);
              let product_models = response.data.product_models;
              this.product_models = product_models;
              this.last_page = product_models.last_page;
              this.footerProps.pagination = {
                page: this.page,
                itemsPerPage: this.itemsPerPage,
                pageStart: product_models.from - 1,
                pageStop: product_models.to,
                pageCount: product_models.last_page,
                itemsLength: product_models.total,
              };

              this.loading = false;
            },
            (error) => {
              this.isUnauthorized(error);
            }
          );
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },
    nextPage() {
      if(this.page < this.last_page )
      {
        this.page = this.page + 1;
      }
      console.log(this.page);
      this.getProductModel();
    },
    prevPage()
    {
      if(this.page > 1 )
      {
        this.page = this.page - 1;
      }

      console.log(this.page);

      this.getProductModel();
    },
    firstPage()
    {
      this.page = 1;
      this.prevBtnDisable = true;
      this.firstBtnDisable = true;
    },

    lastPage()
    {
      this.page = this.last_page;
      this.nexBtnDisable = true;
      this.lastBtnDisable = true;
    },

    editProductModel(item) {
      this.editedIndex = this.product_models.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteProductModel(product_model_id) {
      const data = { product_model_id: product_model_id };
      this.loading = true;
      axios.post("/api/product_model/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "product-model-delete" });
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

          const product_model_id = item.id;
          const index = this.product_models.indexOf(item);

          //Call delete Product Model function
          this.deleteProductModel(product_model_id);

          //Remove item from array product_models
          this.product_models.splice(index, 1);

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
      this.product_modelError = {
        name: [],
      };

      if (!this.$v.$error) {
        this.disabled = true;

        if (this.editedIndex > -1) {
          const data = this.editedItem;
          const product_model_id = this.editedItem.id;

          axios
            .post("/api/product_model/update/" + product_model_id, data)
            .then(
              (response) => {
                if (response.data.success) {
                  // send data to Sockot.IO Server
                  // this.$socket.emit("sendData", { action: "product-model-edit" });

                  Object.assign(
                    this.product_models[this.editedIndex],
                    this.editedItem
                  );
                  this.showAlert();
                  this.close();
                } else {
                  let errors = response.data;
                  let errorNames = Object.keys(response.data);

                  errorNames.forEach((value) => {
                    this.product_modelError[value].push(errors[value]);
                  });
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

          axios.post("/api/product_model/store", data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "product-model-create" });

                this.showAlert();
                this.close();

                //push recently added data from database
                this.product_models.push(response.data.product_models);
              } else {
                let errors = response.data;
                let errorNames = Object.keys(response.data);

                errorNames.forEach((value) => {
                  this.product_modelError[value].push(errors[value]);
                });
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

    clear() {
      this.$v.$reset();
      this.editedItem.name = "";
      this.product_modelError = {
        name: [],
      };
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
          action == "product-model-create" ||
          action == "product-model-edit" ||
          action == "product-model-delete"
        ) {
          this.getProductModel();
        }
      };
    },
    getItemPerPage() {},
  },

  watch: {
    page() {
      this.nextBtnDisable = false;
      this.prevBtnDisable = false;
      this.firstBtnDisable = false;
      this.lastBtnDisable = false;

      if(this.page === 1)
      {
        this.prevBtnDisable = true;
        this.firstBtnDisable = true;
      }

      if(this.page === this.last_page)
      {
        this.nextBtnDisable = true;
        this.lastBtnDisable = true;
      }
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1
        ? "New Product Model"
        : "Edit Product Model";
    },
    product_modelErrors() {
      const errors = [];
      if (!this.$v.editedItem.name.$dirty) return errors;
      !this.$v.editedItem.name.required &&
        errors.push("Product Model is required.");
      return errors;
    },
    activeStatus() {
      if (this.switch1) {
        this.editedItem.active = "Y";
        return " Active";
      } else {
        this.editedItem.active = "N";
        return " Inactive";
      }
    },
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getProductModel();
    // this.websocket();
  },
};
</script>