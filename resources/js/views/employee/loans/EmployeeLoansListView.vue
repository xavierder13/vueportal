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
                  v-if="userPermissions.employee_loans_import"
                >
                  <v-list-item-title>
                    <v-btn
                      color="primary"
                      class="ma-2"
                      width="120px"
                      small
                      @click="importExcel()"
                    >
                      <v-icon class="mr-1" small> mdi-import </v-icon>
                      Import
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="userPermissions.employee_loans_export"
                >
                  <v-list-item-title>
                    <v-btn
                      color="success"
                      class="ma-2"
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
                  v-if="userPermissions.employee_clear_list"
                >
                  <v-list-item-title>
                    <v-btn
                      color="error"
                      class="ma-2"
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
            Employee Loans Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
            ></v-text-field>
            <v-spacer></v-spacer>

            <v-btn
              color="primary"
              fab
              dark
              class="mb-2"
              @click="clear() + (dialog = true)"
              v-if="userPermissions.employee_loans_create"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
          </v-card-title>

          <v-data-table
            :headers="headers"
            :items="employee_loans"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="userPermissions.employee_loans_list"
          >
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editEmployeeLoans(item)"
                v-if="userPermissions.employee_loans_edit"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="userPermissions.employee_loans_delete"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-data-table>

          <v-dialog v-model="dialog" max-width="1000px" persistent>
            <v-card>
              <v-card-title class="mb-0 pb-0">
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>
              <v-divider></v-divider>
              <v-card-text>
                <v-container>
                  <fieldset>
                    <legend class="mb-6">Personal Information</legend>
                    <v-row>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-text-field
                          name="last_name"
                          v-model="editedItem.last_name"
                          :error-messages="lastNameErrors"
                          label="Last Name"
                          @input="$v.editedItem.last_name.$touch()"
                          @blur="$v.editedItem.last_name.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-text-field
                          name="first_name"
                          v-model="editedItem.first_name"
                          :error-messages="firstNameErrors"
                          label="First Name"
                          @input="$v.editedItem.first_name.$touch()"
                          @blur="$v.editedItem.first_name.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-text-field
                          name="middle_name"
                          v-model="editedItem.middle_name"
                          label="Middle Name"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-text-field
                          name="position"
                          v-model="editedItem.position"
                          :error-messages="positionErrors"
                          label="Position"
                          @input="$v.editedItem.position.$touch()"
                          @blur="$v.editedItem.position.$touch()"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col class="mt-0 mb-0 pt-0 pb-0">
                        <v-divider class="mt-0 mb-0 pt-0 pb-0"></v-divider>
                      </v-col>
                    </v-row>
                  </fieldset>
                  <fieldset>
                    <legend class="mb-6 mt-6">Loan Information</legend>
                    <v-row>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-text-field
                          name="loan_type"
                          v-model="editedItem.loan_type"
                          :error-messages="loanTypeErrors"
                          label="Loan Type"
                          @input="$v.editedItem.loan_type.$touch()"
                          @blur="$v.editedItem.loan_type.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-text-field
                          name="Description"
                          v-model="editedItem.description"
                          :error-messages="descriptionErrors"
                          label="Description"
                          @input="$v.editedItem.description.$touch()"
                          @blur="$v.editedItem.description.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-text-field
                          name="ref_no"
                          v-model="editedItem.ref_no"
                          :error-messages="refNoErrors"
                          label="Reference No."
                          @input="$v.editedItem.ref_no.$touch()"
                          @blur="$v.editedItem.ref_no.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-menu
                          v-model="input_date_granted"
                          :close-on-content-click="false"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              name="date_granted"
                              v-model="computedDateGrantedFormatted"
                              label="Date Granted (MM/DD/YYYY)"
                              persistent-hint
                              readonly
                              v-bind="attrs"
                              v-on="on"
                              :error-messages="dateGrantedErrors"
                              @input="$v.editedItem.date_granted.$touch()"
                              @blur="$v.editedItem.date_granted.$touch()"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="editedItem.date_granted"
                            no-title
                            @input="input_date_granted = false"
                          ></v-date-picker>
                        </v-menu>
                      </v-col>         
                    </v-row>
                    <v-row>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="editedItem.principal_loan"
                          label= "Principal Loan"
                          v-bind:properties="{
                            name: 'principal_loan',
                            placeholder: '0.00',
                            error: principalLoanErrors.hasError,
                            messages: principalLoanErrors.errors,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
      
                          }"
                          @input="$v.editedItem.principal_loan.$touch()"
                          @blur="$v.editedItem.principal_loan.$touch()"
                        >
                        </v-text-field-dotnumber>
                      </v-col>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="editedItem.interest"
                          label= "Interest"
                          v-bind:properties="{
                            name: 'interest',
                            placeholder: '0.00',
                            error: interestErrors.hasError,
                            messages: interestErrors.errors,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="$v.editedItem.interest.$touch()"
                          @blur="$v.editedItem.interest.$touch()"
                        >
                        </v-text-field-dotnumber>
                      </v-col>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="editedItem.total_loan"
                          label= "Total Loan"
                          v-bind:properties="{
                            name: 'total_loan',
                            placeholder: '0.00',
                            error: totalLoanErrors.hasError,
                            messages: totalLoanErrors.errors,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="$v.editedItem.total_loan.$touch()"
                          @blur="$v.editedItem.total_loan.$touch()"
                        >
                        </v-text-field-dotnumber>
                      </v-col>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <!-- <v-text-field-money
                          class="pa-0"
                          v-model="editedItem.terms"
                          label="Terms"
                          v-bind:properties="{
                            name: 'terms',
                            placeholder: '0',
                            error: termsErrors.hasError,
                            messages: termsErrors.errors,
                          }"
                          v-bind:options="{
                            length: 16,
                            precision: 0,
                            empty: null,
                          }"
                          @input="$v.editedItem.terms.$touch()"
                          @blur="$v.editedItem.terms.$touch()"
                        >
                        </v-text-field-money> -->
                        <v-text-field-integer
                          v-model="editedItem.terms"
                          label="Terms"
                          v-bind:properties="{
                            error: termsErrors.hasError,
                            messages: termsErrors.errors,
                            placeholder: '0',
                          }"
                          v-bind:options="{
                            inputMask: '#########',
                            outputMask: '#########',
                            empty: null,
                            applyAfter: false,
                          }"
                          @input="$v.editedItem.terms.$touch()"
                          @blur="$v.editedItem.terms.$touch()"
                        />
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-menu
                          v-model="input_period_from"
                          :close-on-content-click="false"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              name="period_from"
                              v-model="computedPeriodFromFormatted"
                              label="Period From (MM/DD/YYYY)"
                              persistent-hint
                              readonly
                              v-bind="attrs"
                              v-on="on"
                              :error-messages="periodFromErrors"
                              @input="$v.editedItem.period_from.$touch()"
                              @blur="$v.editedItem.period_from.$touch()"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="editedItem.period_from"
                            no-title
                            @input="input_period_from = false"
                          ></v-date-picker>
                        </v-menu>
                      </v-col>  
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-menu
                          v-model="input_period_to"
                          :close-on-content-click="false"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              name="period_to"
                              v-model="computedPeriodToFormatted"
                              label="Period To (MM/DD/YYYY)"
                              persistent-hint
                              readonly
                              v-bind="attrs"
                              v-on="on"
                              :error-messages="periodToErrors"
                              @input="$v.editedItem.period_to.$touch()"
                              @blur="$v.editedItem.period_to.$touch()"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="editedItem.period_to"
                            no-title
                            @input="input_period_to = false"
                          ></v-date-picker>
                        </v-menu>
                      </v-col> 
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="editedItem.monthly_amortization"
                          label= "Monthly Amortization"
                          v-bind:properties="{
                            name: 'monthly_amortization',
                            placeholder: '0.00',
                            error: monthlyAmortizationErrors.hasError,
                            messages: monthlyAmortizationErrors.errors,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="$v.editedItem.monthly_amortization.$touch()"
                          @blur="$v.editedItem.monthly_amortization.$touch()"
                        >
                        </v-text-field-dotnumber>
                      </v-col> 
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="editedItem.total_paid"
                          label= "Total Paid"
                          v-bind:properties="{
                            name: 'total_paid',
                            placeholder: '0.00',
                            error: totalPaidErrors.hasError,
                            messages: totalPaidErrors.errors,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="$v.editedItem.total_paid.$touch()"
                          @blur="$v.editedItem.total_paid.$touch()"
                        >
                        </v-text-field-dotnumber>
                      </v-col> 
                    </v-row>
                    <v-row>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="editedItem.balance"
                          label= "Balance"
                          v-bind:properties="{
                            name: 'balance',
                            placeholder: '0.00',
                            error: balanceErrors.hasError,
                            messages: balanceErrors.errors,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="$v.editedItem.balance.$touch()"
                          @blur="$v.editedItem.balance.$touch()"
                        >
                        </v-text-field-dotnumber>
                      </v-col> 
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <!-- <v-text-field-money
                          class="pa-0"
                          v-model="editedItem.remaining_term"
                          label="Remaining Terms"
                          v-bind:properties="{
                            name: 'remaining_term',
                            placeholder: '0',
                            error: remainingTermErrors.hasError,
                            messages: remainingTermErrors.errors,
                          }"
                          v-bind:options="{
                            length: 16,
                            precision: 0,
                            empty: null,
                          }"
                          @input="$v.editedItem.remaining_term.$touch()"
                          @blur="$v.editedItem.remaining_term.$touch()"
                        >
                        </v-text-field-money> -->
                        <v-text-field-integer
                          v-model="editedItem.remaining_term"
                          label="Remaining Terms"
                          v-bind:properties="{
                            error: remainingTermErrors.hasError,
                            messages: remainingTermErrors.errors,
                            placeholder: '0',
                          }"
                          v-bind:options="{
                            inputMask: '#########',
                            outputMask: '#########',
                            empty: null,
                            applyAfter: false,
                          }"
                          @input="$v.editedItem.remaining_term.$touch()"
                          @blur="$v.editedItem.remaining_term.$touch()"
                        />
                      </v-col>
                      <v-col cols="3" class="mt-0 mb-0 pt-0 pb-0">
                        <v-text-field
                          name="or_number"
                          v-model="editedItem.or_number"
                          :error-messages="orNumberErrors"
                          label="OR Number"
                          @input="$v.editedItem.or_number.$touch()"
                          @blur="$v.editedItem.or_number.$touch()"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                  </fieldset>
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
                  :disabled="disabled"
                  class="mb-4 mr-4"
                >
                  Save
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>

          <v-dialog v-model="dialog_import" max-width="500px" persistent>
            <v-card>
              <v-card-title class="mb-0 pb-0">
                <span class="headline">Import Data</span>
              </v-card-title>
              <v-divider></v-divider>
              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col class="mt-0 mb-0 pt-0 pb-0">
                      <v-file-input
                        v-model="file"
                        show-size
                        label="File input"
                        prepend-icon="mdi-paperclip"
                        required
                        :error-messages="fileErrors"
                        @change="$v.file.$touch() + (fileIsEmpty = false)"
                        @blur="$v.file.$touch()"
                        
                      >
                        <template v-slot:selection="{ text }">
                          <v-chip small label color="primary">
                            {{ text }}
                          </v-chip>
                        </template>
                      </v-file-input>
                    </v-col>
                  </v-row>
                  <v-row
                    class="fill-height"
                    align-content="center"
                    justify="center"
                    v-if="uploading"
                  >
                    <v-col class="subtitle-1 text-center" cols="12">
                      Uploading...
                    </v-col>
                    <v-col cols="6">
                      <v-progress-linear
                        color="primary"
                        indeterminate
                        rounded
                        height="6"
                      ></v-progress-linear>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                  color="#E0E0E0"
                  @click="dialog_import = false"
                  class="mb-4"
                >
                  Cancel
                </v-btn>
                <v-btn
                  color="primary"
                  class="mb-4 mr-4"
                  @click="uploadFile()"
                  :disabled="uploadDisabled"
                >
                  Upload
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <v-dialog v-model="dialog_error_list" max-width="1000px" persistent>
            <v-card>
              <v-card-title class="mb-0 pb-0">
                <span class="headline">Error List</span>
                <v-spacer></v-spacer>
                <v-icon @click="dialog_error_list = false"> mdi-close </v-icon>
              </v-card-title>
              <v-divider></v-divider>
              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col>
                      <v-simple-table dense>
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Error Message</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(item, index) in imported_file_errors">
                            <td>{{ index + 1 }}</td>
                            <td v-html="item"></td>
                          </tr>
                        </tbody>
                      </v-simple-table>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>
            </v-card>
          </v-dialog>
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
      last_name: { required },
      first_name: { required },
      position: { required },	
      loan_type: { required },	
      description: { required },
      ref_no: { required },
      date_granted: { required },	
      principal_loan: { required },	
      interest: { required },	
      total_loan: { required },	
      terms: { required },	
      period_from: { required },	
      period_to: { required },	
      monthly_amortization: { required },	
      total_paid: { required },	
      balance: { required },	
      remaining_term: { required },	
      or_number: { required },		
    },
    file: { required },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "branch.name" },
        { text: "Lastname", value: "last_name" },
        { text: "Firstname", value: "first_name" },
        { text: "Middlename", value: "middle_name" },
        { text: "Position", value: "position" },
        { text: "Loan Type", value: "loan_type" },
        { text: "Description", value: "description" },
        { text: "Date Granted", value: "date_granted" },
        { text: "Period From", value: "period_from" },
        { text: "Period To", value: "period_to" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      disabled: false,
      dialog: false,
      employee_loans: [],
      branches: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Employee Loans Lists",
          disabled: false,
          link: "/employee/loans/list",
        },
        {
          text: "View",
          disabled: true,
        },
      ],
      gender_items: [
        { text: "MALE", value: "MALE" },
        { text: "FEMALE", value: "FEMALE" },
      ],
      civil_status_items: [
        { text: "SINGLE", value: "SINGLE" },
        { text: "MARRIED", value: "MARRIED" },
        { text: "DIVORCED", value: "DIVORCED" },
        { text: "WIDOWED", value: "WIDOWED" },
      ],
      loading: true,
      search_branch: "",
      file: [],
      fileIsEmpty: false,
      fileIsInvalid: false,
      uploadDisabled: false,
      uploading: false,
      dialog_import: false,
      dialog_error_list: false,
      errors_array: [],
      branch_id: "",
      editedIndex: -1,
      editedItem: {
        last_name: "",	
        first_name: "",	
        middle_name: "",	
        position: "",	
        loan_type: "",	
        description: "",
        ref_no: "",
        date_granted: "",	
        principal_loan: "",	
        interest: 0.00,	
        total_loan: 0.00,	
        terms: 0.00,	
        period_from: "",	
        period_to: "",	
        monthly_amortization: 0.00,	
        total_paid: 0.00,	
        balance: 0.00,	
        remaining_term: 0,	
        or_number: "",		
      },
      defaultItem: {
        last_name: "",	
        first_name: "",	
        middle_name: "",	
        position: "",	
        loan_type: "",	
        description: "",
        ref_no: "",
        date_granted: "",	
        principal_loan: 0.00,	
        interest: 0.00,	
        total_loan: 0.00,	
        terms: 0.00,	
        period_from: "",	
        period_to: "",	
        monthly_amortization: 0.00,	
        total_paid: 0.00,	
        balance: 0.00,	
        remaining_term: 0,	
        or_number: "",
      },
      input_date_granted: false,
      input_period_from: false,
      input_period_to: false,
    };
  },

  watch: {
    userIsLoaded: {
      handler() {
        if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
          this.getEmployeeLoans();
        }
      },
    },
    userRolesPermissionsIsLoaded: {
      handler() {
        if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
          this.getEmployeeLoans();
        }
      },
    },
  },

  methods: {
    getEmployeeLoans() {
      this.loading = true;
      axios.get("/api/employee_loans/list/view/" + this.branch_id).then(
        (response) => {
          // if user has no permission to view overall list
          
          if (
            !this.userPermissions.employee_loans_list_all &&
            this.user.branch_id != this.branch_id
          ) {
            this.$router.push({ name: "unauthorize" });
          }

          this.employee_loans = response.data.employee_loans;
          this.loading = false;
          
        },
        (error) => {
          this.isUnauthorized(error);
          console.log(error);
        }
      );
    },
    editEmployeeLoans(item) {

      this.editedIndex = this.employee_loans.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;

      let [month, day, year] = this.editedItem.date_granted.split("/");
      this.editedItem.date_granted = `${year}-${month}-${day}`;

      [month, day, year] = this.editedItem.period_from.split("/");
      this.editedItem.period_from = `${year}-${month}-${day}`;

      [month, day, year] = this.editedItem.period_to.split("/");
      this.editedItem.period_to = `${year}-${month}-${day}`;
    },

    deleteEmployeeLoans(employee_loans_id) {
      const data = { employee_loans_id: employee_loans_id };

      axios.post("/api/employee_loans/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "employee-delete" });
          }
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

          const employee_loans_id = item.id;
          const index = this.employee_loans.indexOf(item);

          //Call delete User function
          this.deleteEmployeeLoans(employee_loans_id);

          //Remove item from array employee_loans
          this.employee_loans.splice(index, 1);

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
      this.loading = false;
      this.clear();
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    save() {
      
      this.$v.editedItem.$touch();
      if (!this.$v.$error) {
        this.disabled = true;
        this.overlay = true;

        this.editedItem.branch_id = this.branch_id;

        if (this.editedIndex > -1) {
          const data = this.editedItem;
          const employee_loans_id = this.editedItem.id;

          axios.post("/api/employee_loans/update/" + employee_loans_id, data).then(
            (response) => {

              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "employee-loans-edit" });

                let employee_loans = response.data.employee_loans;
                employee_loans.branch = response.data.branch;
                
                // format date_granted
                let [year, month, day] = employee_loans.date_granted.split("-");
                employee_loans.date_granted = `${month}/${day}/${year}`;

                // format period_from
                [year, month, day] = employee_loans.period_from.split("-");
                employee_loans.period_from = `${month}/${day}/${year}`;

                // format period_to
                [year, month, day] = employee_loans.period_to.split("-");
                employee_loans.period_to = `${month}/${day}/${year}`;

                Object.assign(
                  this.employee_loans[this.editedIndex],
                  employee_loans
                );
                
                this.showAlert();
                this.close();
              } else {
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
        } else {
          const data = this.editedItem;

          axios.post("/api/employee_loans/store", data).then(
            (response) => {

              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "user-create" });

                this.showAlert();
                this.close();

                let employee_loans = response.data.employee_loans;
                
                // format date_granted
                let [year, month, day] = employee_loans.date_granted.split("-");
                employee_loans.date_granted = `${month}/${day}/${year}`;

                // format period_from
                [year, month, day] = employee_loans.period_from.split("-");
                employee_loans.period_from = `${month}/${day}/${year}`;

                // format period_to
                [year, month, day] = employee_loans.period_to.split("-");
                employee_loans.period_to = `${month}/${day}/${year}`;

                //push recently added data from database
                this.employee_loans.push(employee_loans);
              } else {
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

    clearList() {
      if (this.employee_loans.length) {
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

            let data = { branch_id: this.branch_id, clear_list: true };

            axios.post("api/employee_loans/delete", data).then(
              (response) => {
                if (response.data.success) {
                  // send data to Sockot.IO Server
                  // this.$socket.emit("sendData", { action: "product-create" });

                  this.employee_loans = [];

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
                console.log(error);
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
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },

    importExcel() {
      this.dialog_import = true;
      this.file = [];
      this.$v.$reset();
    },

    uploadFile() {
      this.$v.$touch();
      this.fileIsEmpty = false;
      this.fileIsInvalid = false;

      if (!this.$v.file.$error) {
        this.uploadDisabled = true;
        this.uploading = true;
        let formData = new FormData();

        formData.append("file", this.file);

        axios
          .post("api/employee_loans/import_loans/" + this.branch_id, formData, {
            headers: {
              Authorization: "Bearer " + localStorage.getItem("access_token"),
              "Content-Type": "multipart/form-data",
            },
          })
          .then(
            (response) => {
              this.errors_array = [];

              if (response.data.success) {
                // send data to Socket.IO Server
                // this.$socket.emit("sendData", { action: "import-project" });
                this.getEmployeeLoans();
                this.$swal({
                  position: "center",
                  icon: "success",
                  title: "Record has been imported",
                  showConfirmButton: false,
                  timer: 2500,
                });
                this.$v.$reset();
                this.dialog_import = false;
                this.file = [];
                this.fileIsEmpty = false;
              } else if (response.data.error_column) {
                this.errors_array = response.data.error_column;
                this.dialog_error_list = true;
              } else if (response.data.error_row_data) {
                let error_keys = Object.keys(response.data.error_row_data);
                let errors = response.data.error_row_data;
                let field_values = response.data.field_values;
                let row = "";
                let col = "";

                error_keys.forEach((value, index) => {
                  row = value.split(".")[0];
                  col = value.split(".")[1];
                  errors[value].forEach((val, i) => {
                    this.errors_array[index] =
                      "Error on row: <label class='text-info'>" +
                      (parseInt(row) + 1) +
                      "</label>; Column: <label class='text-primary'>" +
                      col +
                      "</label>; Msg: <label class='text-danger'>" +
                      val +
                      "</label>; Value: <label class='text-success'>" +
                      field_values[row][col] +
                      "</label>";
                  });
                });

                this.dialog_error_list = true;
              } else if (response.data.error_empty) {
                this.fileIsEmpty = true;
              } else {
                this.fileIsInvalid = true;
              }

              this.uploadDisabled = false;
              this.uploading = false;

              console.log(response.data);
            },
            (error) => {
              this.isUnauthorized(error);
              this.uploadDisabled = false;
              console.log(error);
            }
          );
      }
    },
    exportData() {
      if (this.employee_loans.length) {
        window.open(
          location.origin + "/api/employee_loans/export_loans/" + this.branch_id,
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
    formatDate(date) {
      if (!date) return null;
      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },
    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;

        if (
          action == "employee-loans-create" ||
          action == "employee-loans-edit" ||
          action == "employee-loans-delete" ||
          action == "employee-loans-import" ||
          action == "employee-loans-clear-list"
        ) {
          this.getEmployeeLoans();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Employee Loan Information" : "Edit Employee Information";
    },
    fileErrors() {
      const errors = [];
      if (!this.$v.file.$dirty) return errors;
      !this.$v.file.required && errors.push("File is required.");
      this.fileIsEmpty && errors.push("File is empty.");
      this.fileIsInvalid &&
        errors.push("File type must be 'xlsx', 'xls' or 'ods'.");
      return errors;
    },
    imported_file_errors() {
      return this.errors_array.sort();
    },
    lastNameErrors() {
      const errors = [];
      if (!this.$v.editedItem.last_name.$dirty) return errors;
      !this.$v.editedItem.last_name.required &&
        errors.push("Last Name is required.");
      return errors;
    },
    firstNameErrors() {
      const errors = [];
      if (!this.$v.editedItem.first_name.$dirty) return errors;
      !this.$v.editedItem.first_name.required &&
        errors.push("First Name is required.");
      return errors;
    },
    positionErrors() {
      const errors = [];
      if (!this.$v.editedItem.position.$dirty) return errors;
      !this.$v.editedItem.position.required &&
        errors.push("Position is required.");
      return errors;
    },
    loanTypeErrors() {
      const errors = [];
      if (!this.$v.editedItem.loan_type.$dirty) return errors;
      !this.$v.editedItem.loan_type.required &&
        errors.push("Loan Type is required.");
      return errors;
    },
    descriptionErrors() {
      const errors = [];
      if (!this.$v.editedItem.description.$dirty) return errors;
      !this.$v.editedItem.description.required &&
        errors.push("Description is required.");
      return errors;
    },
    refNoErrors() {
      const errors = [];
      if (!this.$v.editedItem.ref_no.$dirty) return errors;
      !this.$v.editedItem.ref_no.required &&
        errors.push("Ref No. is required.");
      return errors;
    },
    dateGrantedErrors() {
      const errors = [];
      if (!this.$v.editedItem.date_granted.$dirty) return errors;
      !this.$v.editedItem.date_granted.required && errors.push("Date Granted is required.");
      return errors;
    },
    computedDateGrantedFormatted() {
      return  this.formatDate(this.editedItem.date_granted);
    },
    principalLoanErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.principal_loan.$dirty) return {errors: errors, hasError: hasError};
      
      if(!this.editedItem.principal_loan)
      {
        errors.push("Principal Loan is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    interestErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.interest.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.interest)
      {
        errors.push("Interest is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    totalLoanErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.total_loan.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.total_loan)
      {
        errors.push("Total Loan Loan is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    termsErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.terms.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.terms)
      {
        errors.push("Terms is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    periodFromErrors() {
      const errors = [];
      if (!this.$v.editedItem.period_from.$dirty) return errors;
      !this.$v.editedItem.period_from.required && errors.push("Period From is required.");
      return errors;
    },
    computedPeriodFromFormatted() {
      return  this.formatDate(this.editedItem.period_from);
    },
    periodToErrors() {
      const errors = [];
      if (!this.$v.editedItem.period_to.$dirty) return errors;
      !this.$v.editedItem.period_to.required && errors.push("Period To is required.");
      return errors;
    },
    computedPeriodToFormatted() {
      return  this.formatDate(this.editedItem.period_to);
    },
    monthlyAmortizationErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.monthly_amortization.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.monthly_amortization)
      {
        errors.push("Terms is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    totalPaidErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.total_paid.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.total_paid)
      {
        errors.push("Total Paid is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    balanceErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.balance.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.balance)
      {
        errors.push("Balance is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    remainingTermErrors() {
      const errors = [];
      let hasError = false;

      if (!this.$v.editedItem.remaining_term.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.remaining_term)
      {
        errors.push("Remaining Term is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    orNumberErrors() {
      const errors = [];
      if (!this.$v.editedItem.or_number.$dirty) return errors;
      !this.$v.editedItem.or_number.required &&
        errors.push("Or Number is required.");
      return errors;
    },
    ...mapState("auth", ["user", "userIsLoaded"]),
    ...mapState("userRolesPermissions", [
      "userRoles",
      "userPermissions",
      "userRolesPermissionsIsLoaded",
    ]),
  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.branch_id = this.$route.params.branch_id;
    
    if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
      this.getEmployeeLoans();
    }

    // this.websocket();
  },
};
</script>