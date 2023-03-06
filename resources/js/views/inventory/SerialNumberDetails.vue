<template>
  <div class="flex column">
    <div id="_wrapper" class="pa-5">
      <v-overlay :absolute="absolute" :value="overlay">
        <v-progress-circular
          :size="70"
          :width="7"
          color="primary"
          indeterminate
        ></v-progress-circular>
      </v-overlay>
      <v-main>
        <v-breadcrumbs :items="items">
          <template v-slot:item="{ item }">
            <v-breadcrumbs-item :to="item.link" :disabled="item.disabled">
              {{ item.text.toUpperCase() }}
            </v-breadcrumbs-item>
          </template>
        </v-breadcrumbs>
        <v-card>
          <v-card-title class="mb-0 pb-0">
            <span class="headline">Serial Number Details</span>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="px-6">
            <v-row>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="serial"
                  v-model="serial"
                  label="Serial"
                  append-outer- ='mdi-magnify'
                  :error-messages="serialErrors"
                  @input="$v.serial.$touch() + $v.model.$reset()"
                >
                  <template v-slot:append-outer> 
                    <v-btn small color="primary" class="px-2" @click="searchBySerial()"> 
                      <v-icon small class="mr-1">mdi-magnify</v-icon> 
                      search
                    </v-btn> 
                  </template>
                </v-text-field>
              </v-col>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="model"
                  v-model="editedItem.model"
                  label="Model"
                >
                  <!-- <template v-slot:append-outer> 
                    <v-btn small color="primary" class="px-2"> 
                      <v-icon small class="mr-1">mdi-magnify</v-icon> 
                      search
                    </v-btn> 
                  </template> -->
                </v-text-field>
              </v-col>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="brand"
                  v-model="editedItem.brand"
                  label="Brand"
                  readonly
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="supplier"
                  v-model="editedItem.supplier"
                  label="Supplier"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="item_group"
                  v-model="editedItem.item_group"
                  label="Item Group"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="product_category"
                  v-model="editedItem.product_category"
                  label="Product Category"
                  readonly
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  v-model="datePurchase"
                  label="Date Purchase"
                  prepend-icon="mdi-calendar"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  v-model="dateIssued"
                  label="Date Issued"
                  prepend-icon="mdi-calendar"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="grpo_number"
                  v-model="editedItem.grpo_number"
                  label="GRPO Number"
                  readonly
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="branch"
                  v-model="editedItem.branch"
                  label="Branch"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="company"
                  v-model="editedItem.company"
                  label="Company"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="gi_number"
                  v-model="editedItem.gi_number"
                  label="Goods Issue Number"
                  readonly
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="6" class="my-0 py-0">
                <v-textarea 
                  v-model="editedItem.grpo_remarks"
                  label="GRPO Remarks"
                  readonly
                  rows="4"

                >
                </v-textarea>
              </v-col>
              <v-col cols="6" class="my-0 py-0">
                <v-textarea 
                  v-model="editedItem.gi_remarks"
                  label="Goods Issue Remarks"
                  readonly
                  rows="4"

                >
                </v-textarea>
              </v-col>
            </v-row>
          </v-card-text>
          <v-divider class="mb-3 mt-0"></v-divider>
          <v-card-actions class="pa-0">
            <v-btn class="ml-6 mb-4" @click="clear() + (serial = '')"> Clear </v-btn>
          </v-card-actions>
        </v-card>
      </v-main>
      <v-dialog v-model="dialog" max-width="1200px" persistent>
        <v-card>
          <v-card-title>
            <span class="headline">Products</span>
              <v-spacer></v-spacer>
              <v-text-field
                v-model="search"
                append-icon="mdi-magnify"
                label="Search"
                single-line
              ></v-text-field>
              <v-spacer></v-spacer>

              <v-icon @click="dialog = false">mdi-close</v-icon>
          </v-card-title>
          <v-card-text>
            <v-container>
              <v-data-table
                :headers="headers"
                :items="products"
                :search="search"
                :loading="loading"
                loading-text="Loading... Please wait"
              > 
                <template v-slot:item="{ item }">
                  <tr>
                    <td>
                      {{ item.branch }}
                    </td>
                    <td>
                      {{ item.company }}
                    </td>
                    <td>
                      {{ item.serial }}
                    </td>
                    <td>
                      {{ item.model }}
                    </td>
                    <td>
                      {{ item.brand }}
                    </td>
                    <td>
                      {{ item.supplier }}
                    </td>
                    <td>
                      {{ dateFormat(item.date_purchase) }}
                    </td>
                    <td>
                      {{ dateFormat(item.date_issued) }}
                    </td>
                  </tr>
                </template>
              </v-data-table>
            </v-container>
          </v-card-text>
        </v-card>
      </v-dialog>
    </div>
  </div>
</template>
<script>                                                                                                                                                                                                                                                                                                                                
import axios from "axios";
import { validationMixin } from "vuelidate";
import {
  required,
  maxLength,
  email,
  minLength,
  sameAs,
} from "vuelidate/lib/validators";

export default {
  mixins: [validationMixin],

  validations: {
    serial: { required },
    model: { required }
  },
  data() {
    return {
      absolute: true,
      overlay: false,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Serial Number Details",
          disabled: true,
        },
      ],
      disabled: false,
      editedIndex: -1,
      serial: "",
      model: "",
      editedItem: {
        serial: "",
        branch: "",
        company: "",
        supplier: "",
        brand: "",
        date_issued: "",
        gi_number: "",
        date_purchase: "",
        grpo_number: "",
        item_group: "",
        model: "",
        product_category: "",
        grpo_remarks: "",
      },
      defaultItem: {
        serial: "",
        branch: "",
        company: "",
        supplier: "",
        brand: "",
        date_issued: "",
        gi_number: "",
        date_purchase: "",
        grpo_number: "",
        item_group: "",
        model: "",
        product_category: "",
        grpo_remarks: "",
      },
      dialog: false,
      products: [],
      loading: false,
      search: "",
      headers: [
        { text: "Branch", value: "branch" },
        { text: "Company", value: "company" },
        { text: "Serial", value: "serial" },
        { text: "Model", value: "model" },
        { text: "Brand", value: "brand" },
        { text: "Supplier", value: "supplier" },
        { text: "Date Purchase", value: "date_puchase" },
        { text: "Date Issued", value: "date_issued" },
      ],
    };
  },

  methods: {
    getSerialNumberDetails(data) {

      if (!this.$v.serial.$error)
      {
        this.overlay = true;
        
        axios.post("/api/product/serial_number_details", data).then(
          (response) => {
            console.log(response.data);

            // if products is more than 1 rows then show the product list table for selection
            if(response.data.products.length > 1)
            {
              this.dialog = true;
              this.products = response.data.products;
            }
            
            console.log('products_count', response.data.products.length);

            // if(response.data.product.serial)
            // {
            //   this.editedItem = response.data.product;
            // }
            // else if(!response.data.error)
            // {
            //   this.$swal({
            //     position: "center",
            //     icon: "warning",
            //     title: "No record found",
            //     showConfirmButton: false,
            //     timer: 2500,
            //   });
            // }
            // else
            // {
            //   this.$swal({
            //     position: "center",
            //     icon: "error",
            //     title: "Error",
            //     text: response.data.error,
            //     showConfirmButton: false,
            //     timer: 2500,
            //   });
            // }

            this.overlay = false;

          },
          (error) => {
            this.isUnauthorized(error);
            console.log(error);
          }
        );
      }
      
    },

    searchBySerial(){
      this.$v.model.$reset(); //reset validation for model when serial field is searched
      this.$v.serial.$touch();
      const data = { serial: this.serial }
      this.getSerialNumberDetails(data);
    },

    searchByModel(){
      this.$v.serial.$reset(); //reset validation for serial when product model field is searched
      this.$v.model.$touch();
      this.dialog = true;
      this.loading = true;
      const data = { model: this.model };
      this.getSerialNumberDetails(data);

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

    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },
    dateFormat(date){
  
      if (!date) return null;

      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },
  },
  computed: {
    serialErrors() {
      const errors = [];
      if (!this.$v.serial.$dirty) return errors;
      !this.$v.serial.required && errors.push("Serial is required.");
      return errors;
    },
    modelErrors() {
      const errors = [];
      if (!this.$v.model.$dirty) return errors;
      !this.$v.model.required && errors.push("Model is required.");
      return errors;
    },
    datePurchase() {
      let date = this.editedItem.date_purchase
      if (!date) return null;

      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },
    dateIssued() {
      let date = this.editedItem.date_issued
      if (!date) return null;

      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    }
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
  },
};
</script>