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
            <span class="headline mr-2">TACTICAL REQUISITION </span>
            <v-chip
              :color="
                editedItem.status === 'Pending'
                  ? 'warning'
                  : editedItem.status === 'Approved'
                  ? 'success'
                  : 'error'
              "
              v-if="editedItem.status"
            >
              {{ editedItem.status }}
            </v-chip>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="px-6 pt-0">
            <v-row>
              <v-col cols="8">
                <v-row v-if="user.id === 1">
                  <v-col class="mt-0 mb-0 pt-0 pb-0">
                    <v-autocomplete
                      v-model="editedItem.branch_id"
                      :items="branches"
                      item-text="name"
                      item-value="id"
                      label="Branch"
                      required
                      readonly
                    >
                    </v-autocomplete>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col class="mt-0 mb-0 pt-0 pb-0">
                    <v-autocomplete
                      v-model="editedItem.marketing_event_id"
                      :items="marketing_events"
                      item-text="event_name"
                      item-value="id"
                      label="Event Title"
                      required
                      readonly
                    >
                    </v-autocomplete>
                  </v-col>
                  <v-col class="mt-0 mb-0 pt-0 pb-0">
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
                  <v-col class="mt-0 mb-0 pt-0 pb-0">
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
                  <v-col class="mt-0 mb-0 pt-0 pb-0">
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
                              label="Period From"
                              prepend-icon="mdi-calendar"
                              readonly
                              v-bind="attrs"
                              v-on="on"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="editedItem.period_from"
                            no-title
                            scrollable
                            :max="editedItem.period_to"
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
                              label="Period To"
                              prepend-icon="mdi-calendar"
                              readonly
                              v-bind="attrs"
                              v-on="on"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="editedItem.period_to"
                            no-title
                            scrollable
                            :min="editedItem.period_from"
                          >
                          </v-date-picker>
                        </v-menu>
                      </v-col>
                    </v-row>
                  </v-col>
                  <v-col class="mt-0 mb-0 pt-0 pb-0">
                    <v-row>
                      <v-col>
                        <v-autocomplete
                          v-model="editedItem.operating_from"
                          :items="time_options"
                          label="Operating From"
                          required
                          prepend-icon="mdi-clock"
                          :error-messages="hrFromErrors"
                          @input="$v.editedItem.operating_from.$touch()"
                          @blur="$v.editedItem.operating_from.$touch()"
                        >
                        </v-autocomplete>
                      </v-col>
                      <v-col>
                        <v-autocomplete
                          v-model="editedItem.operating_to"
                          :items="time_options"
                          label="Operating To"
                          required
                          prepend-icon="mdi-clock"
                          :error-messages="hrToErrors"
                          @input="$v.editedItem.operating_to.$touch()"
                          @blur="$v.editedItem.operating_to.$touch()"
                        >
                        </v-autocomplete>
                      </v-col>
                    </v-row>
                  </v-col>
                </v-row>
              </v-col>
              <v-divider vertical></v-divider>
              <v-col>
                <div class="border-1">
                  <p class="font-weight-bold subtitle-1">
                    PLS ATTACH THE FOLLOWING:
                  </p>
                  <p class="font-weight-bold">1. AGING</p>
                  <p class="mb-0 font-weight-bold">
                    2. SUMMARY OF LEDGER PER CATEGORY
                  </p>
                  <p class="mt-0 font-italic font-weight-medium">
                    for mobile, stall exhibit, and appliance tour
                  </p>
                  <p class="font-weight-bold">
                    3. EXPLANATION LETTER
                    <span class="font-weight-medium">
                      if previous activity is below quota</span
                    >
                  </p>
                  <p class="font-weight-bold">
                    4. PICTURES OF PREVIOUS ACTIVITY STATED HEREIN
                  </p>
                </div>
                <v-btn color="primary" small><v-icon small>mdi-attachment</v-icon> Attach Files {{ '(2)' }}</v-btn> 
              </v-col>
            </v-row>
            <v-divider></v-divider>
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
                        {{ !item.amount ? "0.00" : Number(item.amount).toLocaleString('en', numOpts) }}
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
                        {{ !subItem.amount ? "0.00" : Number(subItem.amount).toLocaleString('en', numOpts) }}
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="font-weight-black">
                      <td colspan="5" class="text-right">TOTAL AMOUNT</td>
                      <td>{{ Number(grand_total).toLocaleString('en', numOpts) }}</td>
                    </tr>
                  </tfoot>
                </v-simple-table>
              </v-col>
            </v-row>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions>
            <v-btn
              color="primary"
              @click="showConfirmAlert()"
              :disabled="disabled"
              class="ml-4 mb-4 mr-1"
            >
              Save
            </v-btn>
            <v-btn color="primary" class="mb-4 mr-1" @click="confirmApproval()"> Approve </v-btn>
            <v-btn color="#E0E0E0" to="/tactical_requisition/index" class="mb-4"> Back </v-btn>
            <v-spacer></v-spacer>
            <v-btn color="error" @click="confirmDelete()" class="mb-4 mr-4"> Delete </v-btn>
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
      venue: { required },
      sponsor: { required },
      period_from: { required },
      period_to: { required },
      operating_from: { required },
      operating_to: { required },
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
          text: "Tactical Requisition Lists",
          disabled: false,
          link: "/tactical_requisition/index",
        },
        {
          text: "View Tactical Requisition",
          disabled: true,
        },
      ],
      switch1: true,
      disabled: false,
      branches: [],
      marketing_events: [],
      editedItem: {
        marketing_event: "",
        marketing_event_id: "",
        branch_id: "",
        venue: "",
        sponsor: "",
        expense_particulars: [],
        period_from: new Date(
          Date.now() - new Date().getTimezoneOffset() * 60000
        )
          .toISOString()
          .substr(0, 10),
        period_to: new Date(Date.now() - new Date().getTimezoneOffset() * 60000)
          .toISOString()
          .substr(0, 10),
        operating_from: "",
        operating_to: "",
        status: "",
      },
      defaultItem: {
        marketing_event: "",
        marketing_event_id: "",
        branch_id: "",
        venue: "",
        period: "",
        sponsor: "",
        operating_hrs: "",
        expense_particulars: [],
        period_from: new Date(
          Date.now() - new Date().getTimezoneOffset() * 60000
        )
          .toISOString()
          .substr(0, 10),
        period_to: new Date(Date.now() - new Date().getTimezoneOffset() * 60000)
          .toISOString()
          .substr(0, 10),
        operating_from: "",
        operating_to: "",
        status: "",
      },
      grand_total: "0.00",
      errorFields: [],
      time_options: [],
      date_menu1: false,
      modal: false,
      date_menu2: false,
      expensePaticularHasError: false,
      numOpts: { 
        minimumFractionDigits: 2,
        maximumFractionDigits: 2 
      }
    };
  },

  methods: {
    getTacticalRequisition() {
      const data = {
        tactical_requisition_id: this.$route.params.tactical_requisition_id,
      };

      axios.post("/api/tactical_requisition/edit", data).then(
        (response) => {
          this.branches = response.data.branches;
          this.marketing_events = response.data.marketing_events;

          console.log(response.data);

          const data = response.data.tactical_requisitions;

          this.editedItem.branch_id = data.branch_id;
          this.editedItem.marketing_event_id = data.marketing_event_id;
          this.editedItem.sponsor = data.sponsor;
          this.editedItem.venue = data.venue;
          this.editedItem.operating_from = data.operating_from;
          this.editedItem.operating_to = data.operating_to;
          this.editedItem.status = data.status;
          this.editedItem.expense_particulars = data.tactical_rows;

          this.getMarketingEvent();
          this.computeAmount();
          
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },
    getMarketingEvent() {
      this.errorFields = [];
      this.expensePaticularHasError = false;
      let expense_particulars =
        this.editedItem.expense_particulars;

      this.editedItem.expense_particulars = [];

      expense_particulars.forEach((value, index) => {
        this.editedItem.expense_particulars.push({
          tactical_requisition_row_id: value.id,
          description: value.description,
          resource_person: value.resource_person,
          contact: value.contact,
          qty: value.qty,
          unit_cost: value.unit_cost,
          amount: value.amount,
          expense_sub_particulars: [],
        });


        this.errorFields.push({
          resource_person: null,
          contact: null,
          qty: null,
          unit_cost: null,
          errorSubFields: [],
        });

        value.tactical_sub_rows.forEach((val, i) => {
          this.editedItem.expense_particulars[
            index
          ].expense_sub_particulars.push({
            tactical_requisition_sub_row_id: val.id,
            description: val.description,
            resource_person: val.resource_person,
            contact: val.contact,
            qty: val.qty,
            unit_cost: val.unit_cost,
            amount: val.amount,
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

      if (!this.$v.$error && !this.expensePaticularHasError) {
        this.disabled = true;
        this.overlay = true;

        let tactical_requisition_id =  this.$route.params.tactical_requisition_id;

        const data = this.editedItem;

        axios.post("/api/tactical_requisition/update/" + tactical_requisition_id, data).then(
          (response) => {
            console.log(response);
            if (response.data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "tactical-requisition-edit" });

              this.showAlert();

            } else {
              let errors = response.data;
              let errorNames = Object.keys(response.data);
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

    approveTactical()
    {
      
      let tactical_requisition_id =  this.$route.params.tactical_requisition_id;
      const data = this.editedItem;

      axios.post("/api/tactical_requisition/approve/" + tactical_requisition_id, data).then(
          (response) => {
            console.log(response);
            if (response.data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "tactical-requisition-approve" });

              this.$swal({
                position: "center",
                icon: "success",
                title: "Approval saved!",
                showConfirmButton: false,
                timer: 2500,
              });

            } else {
              let errors = response.data;
              let errorNames = Object.keys(response.data);
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
    },

    confirmApproval(item) {
      this.$swal({
        title: "Approve Tactical Requisition",
        text: "You won't be able to revert this!",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes",
      }).then((result) => {
        // <--

        if (result.value) {
          this.approveTactical();
        }
      });
    },

    showConfirmAlert(item) {
      this.$swal({
        title: "Do you want to save changes?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes",
      }).then((result) => {
        // <--

        if (result.value) {
          this.save();
        }
      });
    },
    
    deleteTacticalRequisition() {

      const data = { tactical_requisition_id: this.$route.params.tactical_requisition_id };

      axios.post("/api/tactical_requisition/delete", data).then(
        (response) => {
          console.log(response);
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "tactical-requisition-delete" });

            this.$swal({
              position: "center",
              icon: "success",
              title: "Record has been deleted",
              showConfirmButton: false,
              timer: 2500,
            });

            setTimeout(() => {
              this.$router.push({ name: 'tactical.index' })
            }, 500);

          }
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    confirmDelete() {
      this.$swal({
        title: "Are you sure you?",
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
          
          this.deleteTacticalRequisition();
          
        }
      });
    },

    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.grand_total = "0.00";
    },
    getFieldValue(item, subItem, fieldName) {
      let expense_particulars = this.editedItem.expense_particulars;
      let index = expense_particulars.indexOf(item);
      let expense_particular = expense_particulars[index];

      // validate parent row if there is no child data
      if (!expense_particular.expense_sub_particulars.length) {
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
      let grand_total = 0;
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
            if (
              this.errorFields[index]["errorSubFields"][i][fieldName] == "error"
            ) {
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
      if (!this.$v.editedItem.branch_id.$dirty) return errors;
      !this.$v.editedItem.branch_id.required &&
        errors.push("Branch is required.");
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
    hrFromErrors() {
      const errors = [];
      if (!this.$v.editedItem.operating_from.$dirty) return errors;
      !this.$v.editedItem.operating_from.required &&
        errors.push("Select time.");
      return errors;
    },
    hrToErrors() {
      const errors = [];
      if (!this.$v.editedItem.operating_to.$dirty) return errors;
      !this.$v.editedItem.operating_to.required && errors.push("Select time.");
      return errors;
    },
    computedPeriodFromFormatted() {
      return this.formatDate(this.editedItem.period_from);
    },
    computedPeriodToFormatted() {
      return this.formatDate(this.editedItem.period_to);
    },
    ...mapState("auth", ["user"]),
  },
  watch: {
    "editedItem.marketing_event"() {
      this.editedItem.marketing_event_id = this.editedItem.marketing_event.id;
    },
    user() {
      this.editedItem.branch_id = this.user.branch_id;
    },
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");

    this.getTacticalRequisition();
    this.timeOptions();
  },
};
</script>