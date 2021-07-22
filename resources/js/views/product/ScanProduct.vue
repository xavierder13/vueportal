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
        <v-card :max-width="switch1 ? '100%' : '500px'">
          <v-card-title class="mb-0 pb-0">
            <span class="headline">Scan Product</span>
            <v-divider vertical class="ml-3 mr-3"></v-divider>
            <v-switch
              class="ma-0 pa-0"
              hide-details=""
              inset
              v-model="switch1"
              @click="clear()"
            ></v-switch>
            <v-chip :color="switch1 ? 'primary' : 'secondary'">
              {{ scanMode }}
            </v-chip>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text>
            <v-row v-if="switch1">
              <!-- if switch1 is true (Multiple Scan) -->
              <v-col>
                <v-simple-table dense>
                  <thead>
                    <tr>
                      <th>Serial</th>
                      <th class="text-left">Model</th>
                      <th class="text-left">Brand</th>
                      <th class="text-left" v-if="user.id === 1">Branch</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(field, index) in products" :key="index">
                      <td>
                        <v-text-field
                          dense
                          name="serial"
                          v-model="field.serial"
                          readonly
                        ></v-text-field>
                      </td>
                      <td>
                        <v-text-field
                          dense
                          name="model"
                          v-model="field.model"
                          :error-messages="errorFields[index]['model']"
                          @keyup="getFieldValue(index, 'model', field.model)"
                          @blur="getFieldValue(index, 'model', field.model)"
                        ></v-text-field>
                      </td>
                      <td>
                        <v-autocomplete
                          dense
                          v-model="field.brand_id"
                          :items="brands"
                          item-text="name"
                          item-value="id"
                          :error-messages="errorFields[index]['brand_id']"
                          @change="
                            getFieldValue(index, 'brand_id', field.brand_id)
                          "
                          @blur="
                            getFieldValue(index, 'brand_id', field.brand_id)
                          "
                        >
                        </v-autocomplete>
                      </td>
                      <td v-if="user.id === 1">
                        <v-autocomplete
                          dense
                          v-model="field.branch_id"
                          :items="branches"
                          item-text="name"
                          item-value="id"
                          :error-messages="errorFields[index]['branch_id']"
                          @change="
                            getFieldValue(index, 'branch_id', field.branch_id)
                          "
                          @blur="
                            getFieldValue(index, 'branch_id', field.branch_id)
                          "
                          v-if="user.id === 1"
                        >
                        </v-autocomplete>
                      </td>
                      <td>
                        <v-btn
                          dense
                          fab
                          dark
                          x-small
                          color="red"
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
            <v-row v-if="productsEmpty">
              <v-col>
                <span class="v-messages error--text"
                  >Please enter products</span
                >
              </v-col>
            </v-row>
            <v-container>
              <div v-if="!switch1">
                <!-- if switch1 is false (Normal Mode) -->
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
                    <v-text-field
                      name="model"
                      label="Model"
                      v-model="editedItem.model"
                      required
                      :error-messages="modelErrors"
                      @input="$v.editedItem.model.$touch()"
                      @blur="$v.editedItem.model.$touch()"
                    ></v-text-field>
                  </v-col>
                </v-row>
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
                      @input="$v.editedItem.brand_id.$touch()"
                      @blur="$v.editedItem.brand_id.$touch()"
                    >
                    </v-autocomplete>
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
              </div>
            </v-container>
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
            <v-btn color="#E0E0E0" to="/product/index" class="mb-4">
              Cancel
            </v-btn>
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
  maxLength,
  email,
  minLength,
  sameAs,
} from "vuelidate/lib/validators";
import { mapState } from "vuex";

export default {
  mixins: [validationMixin],

  validations: {
    editedItem: {
      branch_id: { required },
      brand_id: { required },
      model: { required },
      serial: { required },
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
          link: "/",
        },
        {
          text: "Create Product",
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
      products: [],
      errorFields: [],
      switch1: false,
      productsEmpty: false,
      productsHasError: false,
    };
  },

  methods: {
    getBrand() {
      axios.get("/api/product/create").then(
        (response) => {
          this.brands = response.data.brands;
          this.branches = response.data.branches;
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
      
      // if scanMode is Normal Mode
      if (!this.switch1) {
        this.$v.$touch();
        await this.products.push(this.editedItem);
      }

      this.productsEmpty = await this.products.length ? false : true;
      
      await this.validateProductFields();

      if (!this.$v.$error && !this.productsEmpty && !this.productsHasError) {
        this.disabled = true;
        this.overlay = true;

        const data = { products: this.products };
        
        axios.post("/api/product/store", data).then(
          (response) => {
 
            if (response.data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "product-create" });

              this.showAlert();
              this.clear();
            } else {
              const object_names = Object.keys(response.data);

              // if products array has value
              if (this.products.length) {
                // get the indexes of validated existing code names
                for (let [key, val] of object_names.entries()) {
                  let object = val.split(".");
                  let object_index = object[1];
                  let object_name = object[2];

                  this.errorFields[object_index][object_name] =
                    response.data[val];
                }
              } else {
                this.productsEmpty = true;
              }
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
    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.products = [];
      this.productsEmpty = false;
      this.productsHasError = false; 
    },

    getFieldValue(index, fieldName, value) {
      let fieldValue = value;

      this.products[index][fieldName] = fieldValue;
      this.errorFields[index][fieldName] = fieldValue
        ? ""
        : "This field is required";
    },

    validateProductFields() {
      this.productsHasError = false;

      this.products.forEach((value, index) => {
        let fields = Object.keys(value);
        fields.forEach((val, i) => {
          // if field is empty
          if(!this.products[index][val])
          {
            this.productsHasError = true;
            this.errorFields[index][val] = "This field is required";
          }
          
        })
      });

    },

    removeRow(item) {
      const index = this.products.indexOf(item);

      //Delete rows on the object products
      this.products.splice(index, 1);
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },

    // Create callback function to receive barcode when the scanner is already done
    onBarcodeScanned(barcode) {
      if (this.switch1) {
        this.products.push({
          brand_id: "",
          model: "",
          serial: barcode,
          branch_id: this.user.branch_id,
        });

        this.errorFields.push({
          brand_id: "",
          model: "",
          serial: "",
          branch_id: "",
        });
      } else {
        this.editedItem.serial = barcode;
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
    serialErrors() {
      const errors = [];
      if (!this.$v.editedItem.serial.$dirty) return errors;
      !this.$v.editedItem.serial.required && errors.push("Serial is required.");
      return errors;
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
    this.getBrand();
    this.$barcodeScanner.init(this.onBarcodeScanned);
  
  },
};
</script>