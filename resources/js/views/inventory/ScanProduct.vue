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
            <span class="headline">Product</span>
            <!-- <v-divider vertical class="ml-3"></v-divider> -->
            <v-chip
              class="ml-3 mr-2"
              :color="switch1 ? 'primary' : 'secondary'"
            >
              {{ scanMode }}
            </v-chip>
            <v-divider vertical class="mr-3"></v-divider>
            <v-switch
              class="ma-0 pa-0"
              hide-details=""
              inset
              v-model="switch1"
              @click="clear()"
            ></v-switch>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-6">
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
                    $v.editedItem.brand_id.$touch() + (fieldsActive = true)
                  "
                  @blur="
                    $v.editedItem.brand_id.$touch() + (fieldsActive = false)
                  "
                  @focus="fieldsActive = true"
                  @change="clearSerialExistStatus()"
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
                  @input="$v.editedItem.model.$touch() + (fieldsActive = true) + clearSerialExistStatus()"
                  @blur="$v.editedItem.model.$touch() + (fieldsActive = false)"
                  @focus="fieldsActive = true"
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
                    $v.editedItem.product_category_id.$touch() + (fieldsActive = true)
                  "
                  @blur="
                    $v.editedItem.product_category_id.$touch() + (fieldsActive = false)
                  "
                  @focus="fieldsActive = true"
                  @change="clearSerialExistStatus()"
                >
                </v-autocomplete>
              </v-col>
            </v-row>
            <v-row v-if="!switch1">
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
                  @input="
                    $v.editedItem.branch_id.$touch() + (fieldsActive = true)
                  "
                  @blur="
                    $v.editedItem.branch_id.$touch() + (fieldsActive = false)
                  "
                  @focus="fieldsActive = true"
                  @change="clearSerialExistStatus()"
                  v-if="user.id === 1"
                >
                </v-autocomplete>
              </v-col>
            </v-row>
            
            <v-row v-if="switch1">
              <v-col class="mt-0 mb-0 pt-0 pb-0">
                <v-simple-table dense>
                  <thead class="grey darken-1">
                    <tr>
                      <th class="white--text" width="10px">#</th>
                      <th class="white--text">Serial</th>
                      <th width="10px"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(field, index) in serials"
                      :key="index"
                      :class="
                        errorFields[index].serial.duplicate ||
                        errorFields[index].serial.exist
                          ? 'white--text  red darken-1'
                          : ''
                      "
                    >
                      <td>
                        {{ index + 1 }}
                      </td>
                      <td>
                        <span>{{ field.serial }}</span>
                      </td>
                      <td>
                        <v-btn
                          dense
                          x-small
                          :color="
                            errorFields[index].serial.duplicate ||
                            errorFields[index].serial.exist
                              ? 'white red--text'
                              : 'red white--text'
                          "
                          class="ma-1"
                          @click="removeRow(field)"
                        >
                          <v-icon dark> mdi-minus </v-icon>
                        </v-btn>
                      </td>
                    </tr>
                  </tbody>
                </v-simple-table>
              </v-col>
            </v-row>
            <v-row v-if="serialsEmpty">
              <v-col>
                <span class="v-messages error--text">Please enter serials</span>
              </v-col>
            </v-row>
            <v-row v-if="serialExists || serialHasDuplicate">
              <v-col>
                <span class="v-messages error--text">{{
                  multiSerialErrors
                }}</span>
              </v-col>
            </v-row>
          </v-card-text>

          <v-card-actions>
            <v-btn
              color="primary"
              @click="save()"
              :disabled="disabled"
              class="ml-4 mb-4 mr-1"
            >
              Save
            </v-btn>
            <v-btn color="#E0E0E0" class="mb-4" @click="clear()"> Clear </v-btn>
          </v-card-actions>
        </v-card>
      </v-main>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import {
  required,
  requiredIf,
  maxLength,
  email,
  minLength,
  sameAs,
} from "vuelidate/lib/validators";
import { mapState } from "vuex";
import goTo from "vuetify/lib/services/goto";
export default {
  mixins: [validationMixin],

  validations: {
    editedItem: {
      branch_id: { required },
      brand_id: { required },
      model: { required },
      product_category_id: { required },
      serial: {
        required: requiredIf(function () {
          return this.serialIsRequired;
        }),
      },
    },
  },
  data() {
    return {
      absolute: true,
      overlay: false,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/product/index",
        },
        {
          text: "Scan Product",
          disabled: true,
        },
      ],
      disabled: false,
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
      brands: [],
      branches: [],
      product_categories: [],
      products: [],
      serials: [],
      errorFields: [],
      switch1: false,
      serialsEmpty: false,
      serialExists: false,
      serialHasDuplicate: false,
      fieldsActive: false,
    };
  },

  methods: {
    getProductOptions() {
      axios.get("/api/product/create").then(
        (response) => {
          this.brands = response.data.brands;
          this.branches = response.data.branches;
          this.product_categories = response.data.product_categories;
          this.editedItem.branch_id = response.data.user.branch_id;
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

    async save() {
      this.$v.$touch();

      // if scanMode is Multiple
      if (this.switch1) {
        this.serialsEmpty = (await this.serials.length) ? false : true;
        this.editedItem.serials = await this.serials;

        await this.validateMultiSerial();
      }

      this.editedItem.scan_mode = await this.scanMode;

      if (
        !this.$v.$error &&
        !this.serialsEmpty &&
        !this.serialExists &&
        !this.serialHasDuplicate
      ) {
        this.disabled = true;
        this.overlay = true;

        const data = this.editedItem;

        await axios.post("/api/product/store", data).then(
          (response) => {
            if (response.data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "product-create" });

              this.showAlert();
              this.clear();
            } else if (response.data.existing_products) {

              let products = response.data.existing_products;

              this.serialExists = true;

              products.forEach((value, index) => {
                this.serials.forEach((val, i) => {

                  if (value.serial == val.serial) {
                    this.errorFields[i].serial.exist = true;
                  }
                });
              });
            } else if (response.data.duplicate_serials) {
              let serials = response.data.duplicate_serials;

              serials.forEach((value, index) => {
                this.serials.forEach((val, i) => {
                  if (value == val.serial) {
                    this.errorFields[i].serial.duplicate = true;
                    this.serialHasDuplicate = true;
                  }
                });
              });
            }

            this.overlay = false;
            this.disabled = false;
          },
          (error) => {
            this.isUnauthorized(error);

            this.overlay = false;
            this.disabled = false;
          }
        );
      }
    },

    async validateMultiSerial() {
      this.serialHasDuplicate = await false;
      this.serialExists = await false;

      // set serialExists to false
      await this.errorFields.forEach((value, index) => {
        if (value.serial.exist) {
          this.serialExists = true;
        }
      });

      // refresh error messages for every index
      await this.serials.forEach((value, index) => {
        this.errorFields[index].serial.duplicate = false;
      });

      await this.serials.forEach((value, index) => {
        this.serials.forEach((val, i) => {
          if (value.serial == val.serial && index != i) {
            this.errorFields[i].serial.duplicate = true;
            this.serialExists = true;
          }
        });
      });
    },

    clearSerialExistStatus() {
      let errorFields = this.errorFields
      this.serialExists = false;
      errorFields.forEach((value, index) => {
        this.errorFields[index].serial.exist = false;
      });
    },

    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedItem.branch_id = this.user.branch_id;
      this.serialsEmpty = false;
      this.serialExists = false;
      this.serialHasDuplicate = false;
      this.serials = [];
      this.errorFields = [];
    },

    async removeRow(item) {
      const index = await this.serials.indexOf(item);

      //Delete rows on the object serials
      await this.serials.splice(index, 1);
      await this.errorFields.splice(index, 1);
      await this.validateMultiSerial();
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },

    // Create callback function to receive barcode when the scanner is already done
    onBarcodeScanned(barcode) {
      // if form field is not active then push barcode data
      if (!this.fieldsActive) {
        this.serialsEmpty = false;

        if (this.switch1) {
          this.serials.push({
            serial: barcode,
          });

          this.errorFields.push({
            serial: { duplicate: false, exist: false },
          });

          this.validateMultiSerial();

          // auto scroll down
          goTo(9999, {
            duration: 1000,
            offset: 500,
            easing: "easeInOutCubic",
          });
        } else {
          this.editedItem.serial = barcode;
          this.serialExists = false;
        }
      }

      // do something...
    },
    // Reset to the last barcode before hitting enter (whatever anything in the input box)
    resetBarcode() {
      let barcode = this.$barcodeScanner.getPreviousCode();
      // do something...
    },
  },
  computed: {
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
      !this.$v.editedItem.product_category_id.required && errors.push("Product Category is required.");
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

    multiSerialErrors() {
      let serialExists = false;
      let serialHasDuplicate = false;

      this.errorFields.forEach((value, index) => {
        if (value.serial.duplicate) {
          serialHasDuplicate = true;
        }
        if (value.serial.exist) {
          serialExists = true;
        }
      });

      if (serialExists && serialHasDuplicate) {
        return "There are duplicate and existing serials";
      } else if (serialExists) {
        return "There are existing serials";
      } else if (serialHasDuplicate) {
        return "There are duplicate serials";
      }
    },
    branchErrors() {
      const errors = [];
      if (!this.$v.editedItem.branch_id.$dirty) return errors;
      !this.$v.editedItem.branch_id.required &&
        errors.push("Branch is required.");
      return errors;
    },
    scanMode() {
      if (this.switch1) {
        return " Multiple Scan";
      } else {
        return " Normal Mode";
      }
    },
    serialIsRequired() {
      let isRequired = false;
      if (this.switch1) {
        isRequired = false;
      } else {
        isRequired = true;
      }

      return isRequired;
    },

    ...mapState("auth", ["user"]),
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
    this.getProductOptions();
    this.$barcodeScanner.init(this.onBarcodeScanned);
    this.editedItem.serial = 1233456567
  },
};
</script>