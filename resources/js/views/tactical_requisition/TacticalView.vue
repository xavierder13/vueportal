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
            <v-spacer></v-spacer>
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
                  <v-col class="my-0 py-0">
                    <v-autocomplete
                      v-model="editedItem.branch_id"
                      :items="branches"
                      item-text="name"
                      item-value="id"
                      label="Branch"
                      required
                      :readonly="isReadOnly ? true : user.id === 1 ? false :  true "
                    >
                    </v-autocomplete>
                  </v-col>
                  <v-col class="mb-0 py-0">
                    <v-menu
                      ref="menu"
                      v-model="date_menu_date_submit"
                      :close-on-content-click="true"
                      :return-value.sync="date_menu_date_submit"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                      :disabled="isReadOnly"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          v-model="computedDateSubmitFormatted"
                          label="Date Submitted"
                          prepend-icon="mdi-calendar"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="editedItem.date_submit"
                        no-title
                        scrollable
                        :max="editedItem.date_submit"
                        readonly
                      >
                      </v-date-picker>
                    </v-menu>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col class="my-0 py-0">
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
                  <v-col class="my-0 py-0">
                    <v-text-field
                      name="sponsor"
                      v-model="editedItem.sponsor"
                      :error-messages="sponsorErrors"
                      label="Sponsor"
                      @input="$v.editedItem.sponsor.$touch()"
                      @blur="$v.editedItem.sponsor.$touch()"
                      :readonly="isReadOnly"
                    ></v-text-field>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col class="my-0 py-0">
                    <v-text-field
                      name="venue"
                      v-model="editedItem.venue"
                      :error-messages="venueErrors"
                      label="Venue"
                      @input="$v.editedItem.venue.$touch()"
                      @blur="$v.editedItem.venue.$touch()"
                      :readonly="isReadOnly"
                    ></v-text-field>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col class="my-0 py-0">
                    <v-row>
                      <v-col>
                        <v-menu
                          ref="menu"
                          v-model="date_menu_period_fr"
                          :close-on-content-click="true"
                          :return-value.sync="date_menu_period_fr"
                          transition="scale-transition"
                          offset-y
                          min-width="auto"
                          :disabled="isReadOnly"
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
                          v-model="date_menu_period_to"
                          :close-on-content-click="true"
                          :return-value.sync="date_menu_period_to"
                          transition="scale-transition"
                          offset-y
                          min-width="auto"
                          :disabled="isReadOnly"
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
                  <v-col class="my-0 py-0">
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
                          :readonly="isReadOnly"
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
                          :readonly="isReadOnly"
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
                <div class="d-flex justify-start">
                  <v-btn color="primary" small @click="dialog_attach_file = true">
                    <v-icon small>mdi-attachment</v-icon> Attach Files {{ attachmentLength }}
                  </v-btn> 
                  <p class="ml-2 font-weight-bold font-italic red--text text--darken-1" v-if="fileIsRequired"> 
                    {{ fileErrors }} 
                  </p>
                </div>
              </v-col>
            </v-row>
            <v-divider></v-divider>
            <v-row>
              <v-col class="mb-0 py-0">
                <p class="font-weight-bold subtitle-1">
                  PREVIOUS TACTICAL ACTIVITY
                </p>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="mb-0 py-0">
                <v-menu
                  ref="menu"
                  v-model="date_menu_prev_period_fr"
                  :close-on-content-click="true"
                  :return-value.sync="date_menu_prev_period_fr"
                  transition="scale-transition"
                  offset-y
                  min-width="auto"
                  :disabled="isReadOnly"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="computedPrevPeriodFromFormatted"
                      label="Period From"
                      prepend-icon="mdi-calendar"
                      readonly
                      v-bind="attrs"
                      v-on="on"
                    ></v-text-field>
                  </template>
                  <v-date-picker
                    v-model="editedItem.prev_period_from"
                    no-title
                    scrollable
                    :max="editedItem.prev_period_from"
                  >
                  </v-date-picker>
                </v-menu>
              </v-col>
              <v-col class="mb-0 py-0">
                <v-menu
                  ref="menu"
                  v-model="date_menu_prev_period_to"
                  :close-on-content-click="true"
                  :return-value.sync="date_menu_prev_period_to"
                  transition="scale-transition"
                  offset-y
                  min-width="auto"
                  :disable="isReadOnly"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="computedPrevPeriodToFormatted"
                      label="Period To"
                      prepend-icon="mdi-calendar"
                      readonly
                      v-bind="attrs"
                      v-on="on"
                    ></v-text-field>
                  </template>
                  <v-date-picker
                    v-model="editedItem.prev_period_to"
                    no-title
                    scrollable
                    :max="editedItem.prev_period_to"
                  >
                  </v-date-picker>
                </v-menu>
              </v-col>
              
              <v-col class="my-0 py-0">
                <v-text-field
                  name="prev_venue"
                  v-model="editedItem.prev_venue"
                  label="Venue"
                  :readonly="isReadOnly"
                ></v-text-field>
              </v-col>
              <v-col class="my-0 py-0">
                <v-text-field
                  name="prev_sponsor"
                  v-model="editedItem.prev_sponsor"
                  label="Sponsor"
                  :readonly="isReadOnly"
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0">
                <v-text-field-dotnumber
                  v-model="editedItem.prev_quota"
                  label= 'Quota (PHP)'
                  v-bind:properties="{
                    name: 'prev_quota',
                    placeholder: '0',
                    error: prevQuotaErrors.length ? true : false,
                    messages: prevQuotaErrors,
                    readonly: isReadOnly
                  }"
                  v-bind:options="{
                    length: 16,
                    precision: 0,
                    empty: null,
                    
                  }"
                >
                </v-text-field-dotnumber>
              </v-col>
              <v-col class="my-0 py-0">
                <v-text-field-dotnumber
                  v-model="editedItem.prev_total_sales"
                  label= 'Total Sales (PHP)'
                  v-bind:properties="{
                    name: 'prev_total_sales',
                    placeholder: '0',
                    error: prevTotalSalesErrors.length ? true : false,
                    messages: prevTotalSalesErrors,
                    readonly: isReadOnly
                  }"
                  v-bind:options="{
                    length: 16,
                    precision: 0,
                    empty: null,
                  }"
                >
                </v-text-field-dotnumber>
              </v-col>
              <v-col class="my-0 py-0">
                <v-text-field-dotnumber
                  v-model="editedItem.prev_sales_achievement"
                  label= 'Sales Achievement (PHP)'
                  v-bind:properties="{
                    name: 'prev_sales_achievement',
                    placeholder: '0',
                    error: prevSalesAchvmntErrors.length ? true : false,
                    messages: prevSalesAchvmntErrors,
                    readonly: isReadOnly
                  }"
                  v-bind:options="{
                    length: 16,
                    precision: 0,
                    empty: null,
                  }"
                >
                </v-text-field-dotnumber>
              </v-col>
              <v-col class="my-0 py-0">
                <v-text-field-dotnumber
                  v-model="editedItem.prev_total_expense"
                  label= 'Total Expense (PHP)'
                  v-bind:properties="{
                    name: 'prev_total_expense',
                    placeholder: '0',
                    error: prevTotalExpenseErrors.length ? true : false,
                    messages: prevTotalExpenseErrors,
                    readonly: isReadOnly
                  }"
                  v-bind:options="{
                    length: 16,
                    precision: 0,
                    empty: null,
                  }"
                >
                </v-text-field-dotnumber>
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
                      <td width="150px">UNIT COST (PHP)</td>
                      <td>AMOUNT (PHP)</td>
                    </tr>
                  </thead>
                  <tbody v-for="(item, index) in editedItem.expense_particulars">
                    <tr>
                      <td class="font-weight-bold border-0">
                        {{ item.description }}
                      </td>
                      <template v-if="item.expense_sub_particulars.length === 0">
                        <td class="border-0">
                          <v-text-field
                            name="resource_person"
                            v-model="item.resource_person"
                            dense
                            hide-details
                            outlined
                            @input="getFieldValue(item, '', 'resource_person')"
                            @blur="getFieldValue(item, '', 'resource_person')"
                            :error-messages="errorField(index, 'resource_person')"
                            :readonly="isReadOnly"
                          ></v-text-field>
                        </td>
                        <td class="border-0">
                          <v-text-field
                            name="contact"
                            v-model="item.contact"
                            dense
                            hide-details
                            outlined
                            @input="getFieldValue(item, '', 'contact')"
                            @blur="getFieldValue(item, '', 'contact')"
                            :error-messages="errorField(index, 'contact')"
                            :readonly="isReadOnly"
                          ></v-text-field>
                        </td>
                        <td class="border-0">
                          <v-text-field-money
                            class="mb-2 mt-2 pa-0"
                            v-model="item.qty"
                            v-bind:properties="{
                              name: 'qty',
                              placeholder: '0',
                              'hide-details': true,
                              outlined: true,
                              dense: true,
                              error: errorField(index, 'qty'),
                              messages: '',
                              readonly: isReadOnly
                            }"
                            v-bind:options="{
                              length: 16,
                              precision: 0,
                              empty: null,
                            }"
                            @input="getFieldValue(item, '', 'qty') + computeAmount()"
                            @blur="getFieldValue(item, '', 'qty') + computeAmount()"
                          >
                          </v-text-field-money>
                        </td>
                        <td class="border-0">
                          <v-text-field-dotnumber
                            class="mb-2 mt-2 pa-0"
                            v-model="item.unit_cost"
                            v-bind:properties="{
                              name: 'unit_cost',
                              placeholder: '0.00',
                              'hide-details': true,
                              outlined: true,
                              dense: true,
                              error: errorField(index, 'unit_cost'),
                              messages: '',
                              readonly: isReadOnly
                            }"
                            v-bind:options="{
                              length: 11,
                              precision: 2,
                              empty: null,
                            }"
                            @input="getFieldValue(item, '', 'unit_cost') + computeAmount()"
                            @blur="getFieldValue(item, '', 'unit_cost') + computeAmount()"
                          >
                          </v-text-field-dotnumber>
                        </td>
                        <td class="font-weight-bold border-0">
                          {{ !item.amount ? "0.00" : Number(item.amount).toLocaleString('en', numOpts) }}
                        </td>
                      </template>
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
                          @input="getFieldValue(item, subItem, 'resource_person')"
                          @blur="getFieldValue(item, subItem, 'resource_person')"
                          :error-messages="errorSubField(index, i, 'resource_person')"
                          :readonly="isReadOnly"
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
                          @blur="getFieldValue(item, subItem, 'contact')"
                          :error-messages="errorSubField(index, i, 'contact')"
                          :readonly="isReadOnly"
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
                            error: errorSubField(index, i, 'qty'),
                            messages: '',
                            readonly: isReadOnly
                          }"
                          v-bind:options="{
                            length: 16,
                            precision: 0,
                            empty: null,
                          }"
                          @input="getFieldValue(item, subItem, 'qty') + computeAmount()"
                          @blur="getFieldValue(item, subItem, 'qty') + computeAmount()"
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
                            error: errorSubField(index, i, 'unit_cost'),
                            messages: '',
                            readonly: isReadOnly
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="getFieldValue(item, subItem, 'unit_cost') + computeAmount()"
                          @blur="getFieldValue(item, subItem, 'unit_cost') + computeAmount()"
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
          <v-divider class="mb-3 mt-0"></v-divider>
          <v-card-actions class="pa-0 pl-4 pb-4">
            <template v-if="editedItem.status  !== 'Approved'">
              <v-btn
                color="primary"
                @click="showConfirmAlert()"
                :disabled="disabled"
                v-if="hasPermission('tactical-requisition-edit') && !isApproved"
              >
                Save
              </v-btn>
              <v-btn color="success" @click="confirmApproval()" v-if="hasPermission('tactical-requisition-approve') && !isApproved"> Approve </v-btn>
              <v-btn color="error" @click="confirmDelete()" v-if="hasPermission('tactical-requisition-approve')"> Disapprove </v-btn>
              <v-btn color="error" @click="confirmDelete()" v-if="hasPermission('tactical-requisition-delete') && !isApproved"> Delete </v-btn>
            </template>
            <v-btn color="#E0E0E0" to="/tactical_requisition/index"> Back </v-btn>
          </v-card-actions>
        </v-card>
        <v-dialog v-model="dialog_attach_file" max-width="500px" persistent>
          <v-card>
            <v-card-title class="pa-4">
              <span class="headline">Attachments</span>
              <v-spacer></v-spacer>
              <v-btn @click="dialog_attach_file = false" icon>
                <v-icon> mdi-close-circle </v-icon>
              </v-btn>
            </v-card-title>
            <v-divider class="mt-0"></v-divider>
            <v-card-text>
              <v-container>
                <v-row v-if="tactical_attachments.length">
                  <v-col class="my-0 py-0">
                    <v-simple-table class="elevation-1 file_table" dense>
                      <template v-slot:default>
                        <thead>
                          <tr>
                            <th width="10px"> # </th>
                            <th> Uploaded Files </th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(item, i) in tactical_attachments" :key="item.id">
                            <td>{{ i + 1 }}</td>
                            <td> 
                              <v-btn class="ma-0" small icon color="error" @click="confirmRemoveFile(item)" v-if="hasPermission('tactical-attachment-delete') && !isReadOnly">
                                <v-icon> mdi-close-circle </v-icon> 
                              </v-btn>
                              <v-btn x-small text class="blue--text text--darken-2 ma-0" @click="fileDownload(item)">
                                {{ item.file_name.length > 50 ? item.file_name.substr(0, 40) + "..." : item.file_name }}
                              </v-btn> 
                            </td>
                          </tr>
                        </tbody>
                      </template>
                    </v-simple-table>
                    <!-- <v-list dense>
                      
                      <v-list-item-group>
                        <v-list-item v-for="item in tactical_attachments" :key="item.id" class="pa-0 ma-0">
                          <v-list-item-content class="pa-0 ma-0">
                            <v-list-item-title class="pa-0 ma-0"> 
                              <v-btn class="ma-0" icon color="error" @click="confirmRemoveFile(item)" v-if="hasPermission('tactical-requisition-delete')">
                                <v-icon> mdi-close-circle </v-icon> 
                              </v-btn>
                              <v-btn x-small text class="blue--text text--darken-2 ma-0" @click="fileDownload(item)">
                                {{ item.file_name.length > 50 ? item.file_name.substr(0, 45) + "..." : item.file_name }}
                              </v-btn>
                            </v-list-item-title>
                          </v-list-item-content>
                        </v-list-item>
                      </v-list-item-group>
                    </v-list> -->
                  </v-col>
                </v-row>
                <template v-if="hasPermission('tactical-requisition-edit') && !isApproved">
                  <v-divider class="mt-4 mb-0" v-if="tactical_attachments.length"></v-divider>
                  <v-row v-if="!isApproved ">
                    <v-col class="my-0 py-0">
                      <v-file-input
                        v-model="editedItem.file"
                        show-size
                        label="File input"
                        prepend-icon="mdi-paperclip"
                        required
                        multiple
                      >
                        <template v-slot:selection="{ index, text }">
                          <v-chip small label color="primary" close @click:close="removeFile(index, text)">
                            {{ text }}
                          </v-chip>
                        </template>
                      </v-file-input>
                    </v-col>
                  </v-row>
                </template>
              </v-container>
            </v-card-text>
            <v-divider class="mb-3 mt-0"></v-divider>
            <v-card-actions class="pa-0">
              <v-spacer></v-spacer>
              <!-- <v-btn
                color="#E0E0E0"
                @click="(dialog_attach_file = false)"
                class="mb-4"
              >
                Close
              </v-btn> -->
              <v-btn
                color="primary"
                class="mb-3 mr-4"
                @click="uploadFile()"
              >
                {{ attachmentBtnLabel }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-main>
    </div>
  </div>
</template>
<style>
.file_table th, .file_table td { border:1px solid #dddddd; border-bottom:1px solid #dddddd;}
</style>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import {
  required,
  requiredIf,
} from "vuelidate/lib/validators";
import TimeRangePicker from "vuetify-time-range-picker";
import { mapState, mapGetters } from "vuex";

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
      file: {
        required: requiredIf(function () {
          return this.fileIsRequired;
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
      disabled: false,
      branches: [],
      marketing_events: [],
      tactical_attachments: [],
      editedItem: {
        marketing_event: "",
        marketing_event_id: "",
        branch_id: "",
        venue: "",
        sponsor: "",
        expense_particulars: [],
        date_submit: new Date(
          Date.now() - new Date().getTimezoneOffset() * 60000
        )
          .toISOString()
          .substr(0, 10),
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
        prev_period_from: null,
        prev_period_to: null,
        prev_venue: "",
        prev_sponsor: "",
        prev_quota: "",
        prev_total_sales: "",
        prev_sales_achievement: "",
        prev_total_expense: "",
        status: "",
        file: [],
      },
      defaultItem: {
        marketing_event: "",
        marketing_event_id: "",
        branch_id: "",
        venue: "",
        sponsor: "",
        expense_particulars: [],
        date_submit: new Date(
          Date.now() - new Date().getTimezoneOffset() * 60000
        )
          .toISOString()
          .substr(0, 10),
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
        prev_period_from: null,
        prev_period_to: null,
        prev_venue: "",
        prev_sponsor: "",
        prev_quota: "",
        prev_total_sales: "",
        prev_sales_achievement: "",
        prev_total_expense: "",
        status: "",
        file: [],
      },
      grand_total: "0.00",
      errorFields: [],
      time_options: [],
      date_menu_period_fr: false,
      date_menu_period_to: false,
      date_menu_date_submit: false,
      date_menu_prev_period_fr: false,
      date_menu_prev_period_to: false,
      modal: false,
      expensePaticularHasError: false,
      numOpts: { 
        minimumFractionDigits: 2,
        maximumFractionDigits: 2 
      },
      dialog_attach_file: false,
      tactical_requisition_id: null,
      approved_logs: [],
    };
  },

  methods: {
    getTacticalRequisition() {
      this.tactical_requisition_id = this.$route.params.tactical_requisition_id;

      const data = {
        tactical_requisition_id: this.tactical_requisition_id,
      };

      axios.post("/api/tactical_requisition/edit", data).then(
        (response) => {
          this.branches = response.data.branches;
          this.marketing_events = response.data.marketing_events;

          const data = response.data.tactical_requisitions;
          
          this.editedItem.branch_id = data.branch_id;
          this.editedItem.marketing_event = data.marketing_event;
          this.editedItem.marketing_event_id = data.marketing_event_id;
          this.editedItem.sponsor = data.sponsor;
          this.editedItem.venue = data.venue;
          this.editedItem.operating_from = data.operating_from;
          this.editedItem.operating_to = data.operating_to;
          this.editedItem.prev_period_from = data.prev_period_from;
          this.editedItem.prev_period_to = data.prev_period_to;
          this.editedItem.prev_venue = data.prev_venue;
          this.editedItem.prev_sponsor = data.prev_sponsor;
          this.editedItem.prev_quota = data.prev_quota;
          this.editedItem.prev_total_sales = data.prev_total_sales;
          this.editedItem.prev_sales_achievement = data.prev_sales_achievement;
          this.editedItem.prev_total_expense = data.prev_total_expense;
          this.editedItem.status = data.status;
          this.editedItem.expense_particulars = data.tactical_rows;
       
          this.tactical_attachments = data.tactical_attachments;

          this.approved_logs = data.approved_logs;
      
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

        const data = this.editedItem;

        axios.post("/api/tactical_requisition/update/" + this.tactical_requisition_id, data).then(
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

    uploadFile(){
      if(this.attachmentBtnLabel === 'Upload')
      {
        axios.post("/api/tactical_requisition/add_file/" + this.tactical_requisition_id, this.formData, {
          headers: {
              Authorization: "Bearer " + localStorage.getItem("access_token"),
              "Content-Type": "multipart/form-data",
            }
        }).then(
          (response) => {
            console.log(response.data);
            if (response.data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "tactical-requisition-edit" });
              this.tactical_attachments = response.data.tactical_attachments;
              this.editedItem.file = [];

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
      else
      {
        this.dialog_attach_file = false;
      }
      
    },

    approveTactical()
    {
      
      const data = this.editedItem;

      axios.post("/api/tactical_requisition/approve/" + this.tactical_requisition_id, data).then(
          (response) => {
            console.log(response);
            if (response.data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "tactical-requisition-approve" });

              this.$swal({
                position: "center",
                icon: "success",
                title: "Record has been approved!",
                showConfirmButton: false,
                timer: 2500,
              });

              this.approved_logs = response.data.approved_logs;
              this.editedItem.status = response.data.status;

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

      const data = { tactical_requisition_id: this.this.tactical_requisition_id };

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

    deleteFile(item) {

      const data = { file_id: item.id };
      const index = this.tactical_attachments.indexOf(item);

      axios.post("/api/tactical_requisition/delete_file", data).then(
        (response) => {
          console.log(response);
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "tactical-requisition-edit" });
            
            //Remove item from array tactical_attachements
            this.tactical_attachments.splice(index, 1);

            this.$swal({
              position: "center",
              icon: "success",
              title: "File attachment has been deleted",
              showConfirmButton: false,
              timer: 2500,
            });

          }
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
      
    },

    confirmRemoveFile(item){
      this.$swal({
        title: "Are you sure you?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Delete File!",
      }).then((result) => {
        // <--

        if (result.value) {
          // <-- if confirmed
          
          this.deleteFile(item);
          
        }
      });
    },

    fileDownload(item){
      window.open(location.origin + "/api/tactical_requisition/attachment/download?id=" + item.id, "_blank");
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
      let field_value = expense_particular[fieldName];
      let errorFields = this.errorFields[index];
      let error = "";
    
      // validate parent row if there is no child data
      if (!expense_particular.expense_sub_particulars.length) {
        if (!field_value) {
          error = "error";
        } else {
          // validate unit_cost if numeric
          if (fieldName == "unit_cost") {
            if (field_value % 1 >= 0) {
              error = null;
            } else {
              error = "error";
            }
          } else {
            error = null;
          }
        }
      }

      errorFields[fieldName] = error;

      // input for expense sub particulars
      if (subItem) {
        let expense_sub_particulars =  expense_particulars[index]["expense_sub_particulars"];
        let subIndex = expense_sub_particulars.indexOf(subItem);
        let expense_sub_particular = expense_sub_particulars[subIndex];

        field_value = expense_sub_particular[fieldName]
        error = "";

        if (!field_value) {
          error = "error";
        } else {
          // validate unit_cost if numeric
          if (fieldName == "unit_cost") {
            if (field_value % 1 >= 0) {
              error = null;
            } else {
              error = "error";
            }
          } else {
            error = null;
          }
        }

        errorFields.errorSubFields[subIndex][fieldName] = error;
        
      }
    },
    errorField(index, fieldName) {
      let errorField = this.errorFields[index];
      return errorField ? errorField[fieldName] : null;
    },
    errorSubField(index, subIndex, fieldName) {
      let errorField = this.errorFields[index].errorSubFields[subIndex];

      return errorField ? errorField[fieldName] : null;
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
              this.errorFields[index].errorSubFields[i][fieldName] == "error"
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
    removeFile(index, text) {
      this.editedItem.file.splice(index, 1)
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
    prevQuotaErrors(){
      let errors = [];
      if(!(this.editedItem.prev_quota % 1 >= 0))
      {
        errors.push("Invalid Amount");
      }
      return errors;
    },
    prevTotalSalesErrors(){
      let errors = [];
      if(!(this.editedItem.prev_total_sales % 1 >= 0))
      {
        errors.push("Invalid Amount");
      }
      return errors;
    },
    prevSalesAchvmntErrors(){
      let errors = [];
      if(!(this.editedItem.prev_sales_achievement % 1 >= 0))
      {
        errors.push("Invalid Amount");
      }
      return errors;
    },
    prevTotalExpenseErrors(){
      let errors = [];
      if(!(this.editedItem.prev_total_expense % 1 >= 0))
      {
        errors.push("Invalid Amount");
      }
      return errors;
    },
    fileErrors(){
      if(!this.attachmentLength)
      {
        return "Attachment is required!";
      }
    },
    computedPeriodFromFormatted() {
      return this.formatDate(this.editedItem.period_from);
    },
    computedPeriodToFormatted() {
      return this.formatDate(this.editedItem.period_to);
    },
    computedDateSubmitFormatted() {
      return this.formatDate(this.editedItem.date_submit);
    },
    computedPrevPeriodFromFormatted() {
      return this.formatDate(this.editedItem.prev_period_from);
    },
    computedPrevPeriodToFormatted() {
      return this.formatDate(this.editedItem.prev_period_to);
    },
    formData(){
      let formData = new FormData();

      this.editedItem.file.forEach(val => {
        formData.append('file[]', val);
      });

      return formData;
    },
    fileIsRequired(){
      return this.editedItem.marketing_event.attachment_required == 'Y' ? true : false;
    },
    attachmentLength(){
      let ctr = this.editedItem.file.length + this.tactical_attachments.length;
      let length = "";

      if(ctr)
      {
        length = `(${ctr})`;
      }

      return length;

    },
    attachmentBtnLabel(){
      return this.editedItem.file.length ? 'Upload' : 'OK';
    },
    isApproved(){
      let isApproved = false;
      
      // check if current user has already approved the tactical requisition
      this.approved_logs.forEach(value => {
        if(value.approver_id === this.user.id)
        {
          isApproved = true;
        }
      });

      return isApproved;
    },
    isReadOnly(){
      return this.editedItem.status === 'Approved' || this.editedItem.status === 'Disapproved' ? true : false;
    },
    ...mapState("auth", ["user"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
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