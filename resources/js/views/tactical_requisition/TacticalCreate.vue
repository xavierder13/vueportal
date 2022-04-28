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
            <span class="headline mr-2">TACTICAL REQUISITION</span>
            <v-chip color="primary" v-if="branch"> {{ user.id }} </v-chip>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-6">
            <v-row>
              <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                <v-autocomplete
                  v-model="branch"
                  :items="branches"
                  item-text="name"
                  item-value="id"
                  label="Branch"
                  return-object
                  required
                  :error-messages="branchErrors"
                  @input="$v.branch.$touch()"
                  @blur="$v.branch.$touch()"
                >
                </v-autocomplete>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                <v-autocomplete
                  v-model="editedItem.marketing_event"
                  :items="marketing_events"
                  item-text="event_name"
                  item-value="id"
                  label="Event Title"
                  return-object
                  required
                  :error-messages="eventTitleErrors"
                  @input="$v.editedItem.marketing_event.$touch()"
                  @blur="$v.editedItem.marketing_event.$touch()"
                  @change="getMarketingEvent()"
                >
                </v-autocomplete>
              </v-col>
              <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                <v-text-field
                  name="sponsor"
                  v-model="editedItem.sponsor"
                  :error-messages="sponsorErrors"
                  label="Sponsor"
                  @input="$v.editedItem.sponsor.$touch()"
                  @blur="$v.editedItem.sponsor.$touch()"
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="6" class="mt-0 mb-0 pt-0 pb-0">
                <v-text-field
                  name="venue"
                  v-model="editedItem.venue"
                  :error-messages="venueErrors"
                  label="Venue"
                  @input="$v.editedItem.venue.$touch()"
                  @blur="$v.editedItem.venue.$touch()"
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                <v-row>
                  <v-col>
                    <v-menu
                      ref="menu"
                      v-model="date_menu1"
                      :close-on-content-click="true"
                      :return-value.sync="date_menu1"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          v-model="computedPeriodFromFormatted"
                          label="Date From"
                          prepend-icon="mdi-calendar"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="period_date1"
                        no-title
                        scrollable
                        :max="period_date2"
                      >
                      </v-date-picker>
                    </v-menu>
                  </v-col>
                  <v-col>
                    <v-menu
                      ref="menu"
                      v-model="date_menu2"
                      :close-on-content-click="true"
                      :return-value.sync="date_menu2"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          v-model="computedPeriodToFormatted"
                          label="Date To"
                          prepend-icon="mdi-calendar"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="period_date2"
                        no-title
                        scrollable
                        :min="period_date1"
                      >
                      </v-date-picker>
                    </v-menu>
                  </v-col>
                </v-row>
              </v-col>
              <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                <v-row>
                  <v-col>
                    <v-autocomplete
                      v-model="hr_from"
                      :items="time_options"
                      label="Time From"
                      required
                      prepend-icon="mdi-clock"
                      :error-messages="hrFromErrors"
                      @input="$v.hr_from.$touch()"
                      @blur="$v.hr_from.$touch()"
                    >
                    </v-autocomplete>
                  </v-col>
                  <v-col>
                    <v-autocomplete
                      v-model="hr_to"
                      :items="time_options"
                      label="Time To"
                      required
                      prepend-icon="mdi-clock"
                      :error-messages="hrToErrors"
                      @input="$v.hr_to.$touch()"
                      @blur="$v.hr_to.$touch()"
                    >
                    </v-autocomplete>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
            <v-row>
              <v-col>
                <v-simple-table>
                  <thead class="grey darken-1 white--text font-weight-bold">
                    <tr>
                      <td>PARTICULARS</td>
                      <td>RESOURCE PERSON</td>
                      <td>CONTACT DETAILS</td>
                      <td width="110px">QTY</td>
                      <td width="150px">UNIT COST</td>
                      <td>AMOUNT</td>
                    </tr>
                  </thead>
                  <tbody
                    v-for="(item, index) in editedItem.expense_particulars"
                  >
                    <tr>
                      <td class="font-weight-bold border-0">
                        {{ item.description }}
                      </td>
                      <td
                        class="border-0"
                        v-if="item.expense_sub_particulars.length === 0"
                      >
                        <v-text-field
                          name="resource_person"
                          v-model="item.resource_person"
                          dense
                          hide-details
                          outlined
                          @input="getFieldValue(item, '', 'resource_person')"
                          :error-messages="
                            errorFields[index]
                              ? errorFields[index]['resource_person']
                              : null
                          "
                        ></v-text-field>
                      </td>
                      <td
                        class="border-0"
                        v-if="item.expense_sub_particulars.length === 0"
                      >
                        <v-text-field
                          name="contact"
                          v-model="item.contact"
                          dense
                          hide-details
                          outlined
                          @input="getFieldValue(item, '', 'contact')"
                          :error-messages="
                            errorFields[index]
                              ? errorFields[index]['contact']
                              : null
                          "
                        ></v-text-field>
                      </td>
                      <td
                        class="border-0"
                        v-if="item.expense_sub_particulars.length === 0"
                      >
                        <v-text-field-money
                          class="mb-2 mt-2 pa-0"
                          v-model="item.qty"
                          v-bind:properties="{
                            name: 'qty',
                            placeholder: '0',
                            'hide-details': true,
                            outlined: true,
                            dense: true,
                            error: errorFields[index]
                              ? errorFields[index]['qty']
                              : null,
                            messages: '',
                          }"
                          v-bind:options="{
                            length: 16,
                            precision: 0,
                            empty: null,
                          }"
                          @input="
                            getFieldValue(item, '', 'qty') + computeAmount()
                          "
                        >
                        </v-text-field-money>
                      </td>
                      <td
                        class="border-0"
                        v-if="item.expense_sub_particulars.length === 0"
                      >
                        <v-text-field-dotnumber
                          class="mb-2 mt-2 pa-0"
                          v-model="item.unit_cost"
                          v-bind:properties="{
                            name: 'unit_cost',
                            placeholder: '0.00',
                            'hide-details': true,
                            outlined: true,
                            dense: true,
                            error: errorFields[index]
                              ? errorFields[index]['unit_cost']
                              : null,
                            messages: '',
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="
                            getFieldValue(item, '', 'unit_cost') +
                              computeAmount()
                          "
                        >
                        </v-text-field-dotnumber>
                      </td>
                      <td
                        class="font-weight-bold border-0"
                        v-if="item.expense_sub_particulars.length === 0"
                      >
                        {{ !item.amount ? "0.00" : item.amount }}
                      </td>
                    </tr>
                    <tr v-for="(subItem, i) in item.expense_sub_particulars">
                      <td class="border-0">
                        <span class="ml-12">{{ subItem.description }}</span>
                      </td>
                      <td class="border-0">
                        <v-text-field
                          name="resource_person"
                          v-model="subItem.resource_person"
                          dense
                          hide-details
                          outlined
                          @input="
                            getFieldValue(item, subItem, 'resource_person')
                          "
                          :error-messages="
                            errorFields[index]['errorSubFields'][i]
                              ? errorFields[index]['errorSubFields'][i][
                                  'resource_person'
                                ]
                              : null
                          "
                        ></v-text-field>
                      </td>
                      <td class="border-0">
                        <v-text-field
                          name="contact"
                          v-model="subItem.contact"
                          dense
                          hide-details
                          outlined
                          @input="getFieldValue(item, subItem, 'contact')"
                          :error-messages="
                            errorFields[index]['errorSubFields'][i]
                              ? errorFields[index]['errorSubFields'][i][
                                  'contact'
                                ]
                              : null
                          "
                        ></v-text-field>
                      </td>
                      <td class="border-0">
                        <v-text-field-money
                          class="mb-2 mt-2 pa-0"
                          v-model="subItem.qty"
                          v-bind:properties="{
                            name: 'qty',
                            placeholder: '0',
                            'hide-details': true,
                            outlined: true,
                            dense: true,
                            error: errorFields[index]['errorSubFields'][i]
                              ? errorFields[index]['errorSubFields'][i]['qty']
                              : null,
                            messages: '',
                          }"
                          v-bind:options="{
                            length: 16,
                            precision: 0,
                            empty: null,
                          }"
                          @input="
                            getFieldValue(item, subItem, 'qty') +
                              computeAmount()
                          "
                        >
                        </v-text-field-money>
                      </td>
                      <td class="border-0">
                        <v-text-field-dotnumber
                          class="mb-2 mt-2 pa-0"
                          v-model="subItem.unit_cost"
                          v-bind:properties="{
                            name: 'unit_cost',
                            placeholder: '0.00',
                            'hide-details': true,
                            outlined: true,
                            dense: true,
                            error: errorFields[index]['errorSubFields'][i]
                              ? errorFields[index]['errorSubFields'][i][
                                  'unit_cost'
                                ]
                              : null,
                            messages: '',
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="
                            getFieldValue(item, subItem, 'unit_cost') +
                              computeAmount()
                          "
                        >
                        </v-text-field-dotnumber>
                      </td>
                      <td class="font-weight-bold border-0">
                        {{ !subItem.amount ? "0.00" : subItem.amount }}
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="font-weight-black">
                      <td colspan="5" class="text-right">TOTAL AMOUNT</td>
                      <td>{{ grand_total }}</td>
                    </tr>
                  </tfoot>
                </v-simple-table>
              </v-col>
            </v-row>
          </v-card-text>

          <v-card-actions>
            <v-btn
              color="primary"
              @click="save"
              :disabled="disabled"
              class="ml-4 mb-4 mr-1"
            >
              Save
            </v-btn>
            <v-btn color="#E0E0E0" to="/" class="mb-4"> Cancel </v-btn>
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
import TimeRangePicker from "vuetify-time-range-picker";
import { mapState } from "vuex";

export default {
  mixins: [validationMixin],
  components: {
    TimeRangePicker,
  },
  validations: {
    editedItem: {
      marketing_event: { required },
      branch_id: { required },
      venue: { required },
      period: { required },
      sponsor: { required },
      operating_hrs: { required },
    },
    hr_from: { required },
    hr_to: { required },
    branch: { required },
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
          text: "Create Tactical Requisition",
          disabled: true,
        },
      ],
      switch1: true,
      disabled: false,
      branches: [],
      marketing_events: [],
      editedItem: {
        marketing_event: "",
        branch_id: 1,
        venue: "",
        period: "",
        sponsor: "",
        operating_hrs: "",
        expense_particulars: [],
      },
      defaultItem: {
        marketing_event: "",
        branch_id: 1,
        venue: "",
        period: "",
        sponsor: "",
        operating_hrs: "",
        expense_particulars: [],
      },
      grand_total: "0.00",
      errorFields: [],
      time_options: [],
      hr_from: "",
      hr_to: "",
      period_date1: new Date(
        Date.now() - new Date().getTimezoneOffset() * 60000
      )
        .toISOString()
        .substr(0, 10),
      period_date2: new Date(
        Date.now() - new Date().getTimezoneOffset() * 60000
      )
        .toISOString()
        .substr(0, 10),
      date_menu1: false,
      modal: false,
      date_menu2: false,
      expensePaticularHasError: false,
      branch: "",
    };
  },

  methods: {
    getTacticalOptions() {
      axios.get("/api/tactical_requisition/create").then(
        (response) => {
          this.branches = response.data.branches;
          this.marketing_events = response.data.marketing_events;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },
    getMarketingEvent() {
      let expense_particulars =
        this.editedItem.marketing_event.expense_particulars;

      this.editedItem.expense_particulars = [];

      expense_particulars.forEach((value, index) => {
        this.editedItem.expense_particulars.push({
          expense_particular_id: value.id,
          description: value.description,
          resource_person: "",
          contact: "",
          qty: "",
          unit_cost: "",
          amount: "",
          expense_sub_particulars: [],
        });

        this.errorFields.push({
          resource_person: null,
          contact: null,
          qty: null,
          unit_cost: null,
          errorSubFields: [],
        });

        value.expense_sub_particulars.forEach((val, i) => {
          this.editedItem.expense_particulars[
            index
          ].expense_sub_particulars.push({
            expense_sub_particular_id: val.id,
            description: val.description,
            resource_person: "",
            contact: "",
            qty: "",
            unit_cost: "",
            amount: "",
          });

          this.errorFields[index].errorSubFields.push({
            resource_person: null,
            contact: null,
            qty: null,
            unit_cost: null,
          });
        });
      });
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

    save() {
      this.$v.$touch();

      this.validateExpenseParticulars();

      console.log(this.$v.$error);
      console.log(this.expensePaticularHasError);

    console.log(this.editedItem.marketing_event);
    console.log(this.editedItem.branch_id);
    console.log(this.editedItem.venue);
    console.log(this.editedItem.venue);
    console.log(this.editedItem.period);
    console.log(this.editedItem.sponsor);
    console.log(this.editedItem.operating_hrs);
    console.log(this.hr_from);
    console.log(this.hr_to);
    console.log(this.branch);



      // if (!this.$v.$error) {
      //   this.disabled = true;
      //   this.overlay = true;

      //   const data = this.editedItem;

      //   axios.post("/api/user/store", data).then(
      //     (response) => {
      //       if (response.data.success) {
      //         // send data to Sockot.IO Server
      //         // this.$socket.emit("sendData", { action: "user-create" });

      //         this.showAlert();
      //         this.clear();

      //         //push recently added data from database
      //         this.users.push(response.data.user);
      //       } else {
      //         let errors = response.data;
      //         let errorNames = Object.keys(response.data);

      //         errorNames.forEach((value) => {
      //           this.userError[value].push(errors[value]);
      //         });
      //       }
      //       this.overlay = false;
      //       this.disabled = false;
      //     },
      //     (error) => {
      //       this.isUnauthorized(error);

      //       this.overlay = false;
      //       this.disabled = false;
      //     }
      //   );
      // }
    },
    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.grand_total = 0;
    },
    getFieldValue(item, subItem, fieldName) {
      let expense_particulars = this.editedItem.expense_particulars;
      let index = expense_particulars.indexOf(item);
      let expense_particular = expense_particulars[index];

      if (!expense_particular[fieldName]) {
        this.errorFields[index][fieldName] = "error";
      } else {
        // validate unit_cost if numeric
        if (fieldName == "unit_cost") {
          if (expense_particular[fieldName] % 1 >= 0) {
            this.errorFields[index][fieldName] = null;
          } else {
            this.errorFields[index][fieldName] = "error";
          }
        } else {
          this.errorFields[index][fieldName] = null;
        }
      }

      // input for expense sub particulars
      if (subItem) {
        let expense_sub_particulars =
          expense_particulars[index]["expense_sub_particulars"];
        let subIndex = expense_sub_particulars.indexOf(subItem);
        let expense_sub_particular = expense_sub_particulars[subIndex];

        if (!expense_sub_particular[fieldName]) {
          this.errorFields[index]["errorSubFields"][subIndex][fieldName] =
            "error";

        } else {
          // validate unit_cost if numeric
          if (fieldName == "unit_cost") {
            if (expense_sub_particular[fieldName] % 1 >= 0) {
              this.errorFields[index]["errorSubFields"][subIndex][fieldName] =
                null;
            } else {
              this.errorFields[index]["errorSubFields"][subIndex][fieldName] =
                "error";
            }
          } else {
            this.errorFields[index]["errorSubFields"][subIndex][fieldName] =
              null;
          }
        }
      }
    },
    computeAmount() {
      let expense_particulars = this.editedItem.expense_particulars;
      let grand_total = 0.0;
      let decimal_length = 2;

      expense_particulars.forEach((value, index) => {
        let qty = value.qty;
        let unit_cost = value.unit_cost;

        if (!qty) {
          qty = "0.00";
        }

        if (!unit_cost) {
          unit_cost = "0.00";
        }

        // if unit_cost has decimal then get the number of decimal places
        if (unit_cost.split(".")[1]) {
          decimal_length = unit_cost.split(".")[1].length;

          if (decimal_length === 1) {
            decimal_length = 2;
          }
        }

        let amount = qty * parseFloat(unit_cost);

        if (!amount) {
          amount = 0.0;
        }

        grand_total += amount;

        value.amount = amount.toFixed(decimal_length);

        // compute amount for expense sub particulars
        value.expense_sub_particulars.forEach((val, i) => {
          qty = val.qty;
          unit_cost = val.unit_cost;

          if (!qty) {
            qty = "0.00";
          }

          if (!unit_cost) {
            unit_cost = "0.00";
          }

          // if unit_cost has decimal then get the number of decimal places
          if (unit_cost.split(".")[1]) {
            decimal_length = unit_cost.split(".")[1].length;

            if (decimal_length === 1) {
              decimal_length = 2;
            }
          }

          let amount = qty * parseFloat(unit_cost);

          if (!amount) {
            amount = 0.0;
          }

          grand_total += amount;

          val.amount = amount.toFixed(decimal_length);
        });
      });

      // get the number of decimals of grand total
      if (String(grand_total).split(".")[1]) {
        decimal_length = String(grand_total).split(".")[1].length;
      } else {
        decimal_length = 2;
      }

      this.grand_total = grand_total.toFixed(decimal_length);
    },
    validateExpenseParticulars() {
      let expense_particulars = this.editedItem.expense_particulars;
      let object_names = "";

      expense_particulars.forEach((value, index) => {
        object_names = Object.keys(expense_particulars[index]);
        object_names.forEach((fieldName) => {
          this.getFieldValue(value, "", fieldName);
        });
        // validate expense sub particulars fields
        value.expense_sub_particulars.forEach((val, i) => {
          object_names = Object.keys(expense_particulars[index]);
          object_names.forEach((fieldName) => {
            this.getFieldValue(value, val, fieldName);
          });
        });
      });

      this.expensePaticularHasError = false;

      // check/scan if field has error on errorFields variable
      this.errorFields.forEach((value, index) => {
        object_names = Object.keys(value);
        object_names.forEach((fieldName) => {
          if (this.errorFields[index][fieldName] == "error") {
            this.expensePaticularHasError = true;
          }
        });
        value.errorSubFields.forEach((val, i) => {
          object_names = Object.keys(value);
          object_names.forEach((fieldName) => {
            if (this.errorFields[index]['errorSubFields'][i][fieldName] == "error") {
              this.expensePaticularHasError = true;
            }
          });
        });
      });

    },
    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },
    timeOptions() {
      let ctr = 24;

      for (let hr = 1; hr <= 24; hr++) {
        let padStart = "";
        padStart = String(hr).padStart(2, "0") + ":00";

        this.time_options.push(padStart);
      }
    },
    formatDate(date) {
      if (!date) return null;

      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },
  },
  computed: {
    eventTitleErrors() {
      const errors = [];
      if (!this.$v.editedItem.marketing_event.$dirty) return errors;
      !this.$v.editedItem.marketing_event.required &&
        errors.push("Event Title is required.");
      return errors;
    },
    branchErrors() {
      const errors = [];
      if (!this.$v.branch.$dirty) return errors;
      !this.$v.branch.required && errors.push("Branch is required.");
      return errors;
    },
    periodErrors() {
      const errors = [];
      if (!this.$v.editedItem.period.$dirty) return errors;
      !this.$v.editedItem.period.required && errors.push("Period is required.");
      return errors;
    },
    venueErrors() {
      const errors = [];
      if (!this.$v.editedItem.venue.$dirty) return errors;
      !this.$v.editedItem.venue.required && errors.push("Venue is required.");
      return errors;
    },
    sponsorErrors() {
      const errors = [];
      if (!this.$v.editedItem.sponsor.$dirty) return errors;
      !this.$v.editedItem.sponsor.required &&
        errors.push("Sponsor is required.");
      return errors;
    },
    operatingHrsErrors() {
      const errors = [];
      if (!this.$v.editedItem.operating_hrs.$dirty) return errors;
      !this.$v.editedItem.operating_hrs.required &&
        errors.push("Operating Hours is required.");
      return errors;
    },
    hrFromErrors() {
      const errors = [];
      if (!this.$v.hr_from.$dirty) return errors;
      !this.$v.hr_from.required && errors.push("Select time.");
      return errors;
    },
    hrToErrors() {
      const errors = [];
      if (!this.$v.hr_to.$dirty) return errors;
      !this.$v.hr_to.required && errors.push("Select time.");
      return errors;
    },

    computedPeriodFromFormatted() {
      return this.formatDate(this.period_date1);
    },
    computedPeriodToFormatted() {
      return this.formatDate(this.period_date2);
    },
    ...mapState("auth", ["user"]),
  },
  watch: {
    branch() {
      this.editedItem.branch_id = this.branch.id;
      console.log(this.editedItem.branch_id);
    },
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");

    this.getTacticalOptions();
    this.timeOptions();
  },
};
</script>