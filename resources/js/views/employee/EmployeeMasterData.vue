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
          <v-menu offset-y :close-on-content-click="false">
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
                v-if="hasPermission('employee-master-data-import')"
              >
                <v-list-item-title>
                  <v-btn
                    class="mx-1 white--text"
                    color="primary"
                    width="100px"
                    x-small
                    @click="importExcel()"
                  >
                    <v-icon class="mr-1" x-small> mdi-import </v-icon>
                    Import
                  </v-btn>
                </v-list-item-title>
              </v-list-item>
              <v-list-item
                class="ma-0 pa-0"
                style="min-height: 25px"
                v-if="hasPermission('employee-master-data-export')"
              >
                <v-list-item-title>
                  <v-btn
                    class="mx-1 white--text"
                    color="success"
                    width="100px"
                    x-small
                    @click="exportData()"
                  >
                    <v-icon class="mr-1" x-small> mdi-microsoft-excel </v-icon>
                    Export
                  </v-btn>
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
        <v-card>
          <v-data-table
            :headers="tableHeaders"
            :items="employees"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
          > 
            <template v-slot:top>
              <v-toolbar flat class="my-2 pt-2">
                <v-toolbar-title class="mt-4">Employee Master Data </v-toolbar-title>
                <v-divider vertical class="ma-2 ml-4" thickness="20px"></v-divider>
                
                <v-tooltip top v-if="hasPermission('employee-master-data-create')">
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn 
                      small 
                      class="mx-2 mt-4" 
                      color="primary" 
                      rounded 
                      fab
                      v-bind="attrs" v-on="on"
                      @click="clear() + (dialog = true)"
                    >
                        <v-icon>mdi-plus</v-icon> 
                    </v-btn>
                  </template>
                  
                  <span>Add Data</span>
                </v-tooltip>
                <v-tooltip top>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn 
                      small 
                      class="mr-2 mt-4 white--text" 
                      color="#AB47BC" 
                      rounded 
                      fab 
                      @click="getEmployee()"
                      :disabled="loading"
                      v-bind="attrs" v-on="on"
                    > 
                      <v-icon>mdi-refresh</v-icon> 
                    </v-btn>
                  </template>
                  <span>Refresh Data</span>
                </v-tooltip> 
                <v-spacer></v-spacer>
                <v-text-field
                  class="mr-2"
                  v-model="search"
                  append-icon="mdi-magnify"
                  label="Search"
                  single-line
                  hide-details=""
                ></v-text-field>
                
              </v-toolbar>
              <v-toolbar flat class="ma-0 pa-0" extended extension-height="30px">
                <v-autocomplete
                  class="mt-8"
                  v-model="selectedTableHeaders"
                  :items="headerItems"
                  item-text="text"
                  item-value="name"
                  label="Table Columns"
                  multiple
                  hide-details=""
                  item-disabled="disable"
                  return-object
                  chips
                >
                  <template v-slot:selection="data">
                    <v-chip
                      v-bind="data.attrs"
                      :input-value="data.selected"
                      :close="selectedTableHeaders.length > 1 ? true : false"
                      @click="data.select"
                      @click:close="removeSelectedHeader(data.item)"
                    >
                      {{ data.item.text }}
                    </v-chip>
                  </template>
                </v-autocomplete>
              </v-toolbar>
            </template>
            <template v-slot:item.date_employed="{ item }">
              {{ formatDate(item.date_employed) }}
            </template>
            <template v-slot:item.date_resigned="{ item }">
              {{ formatDate(item.date_resigned) }}
            </template>
            <template v-slot:item.active="{ item }">
              <v-chip small :color="item.active ? 'primary' : 'secondary'">
                {{ item.active ? 'Active' : 'Inactive' }}
              </v-chip>
            </template>
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editEmployee(item)"
                v-if="hasPermission('employee-master-data-edit')"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="hasAnyPermission('employee-master-data-delete')"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-data-table>
          <v-dialog v-model="dialog" max-width="1200px" persistent>
            <v-card>
              <v-card-title class="pa-4">
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>
              <v-divider class="mt-0"></v-divider>
              <v-card-text>
                <v-container>
                  <fieldset>
                    <legend class="mb-6">Personal Information</legend>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="employee_code"
                          v-model="editedItem.employee_code"
                          :error-messages="employeeCodeErrors"
                          label="Employee Code"
                          @input="$v.editedItem.employee_code.$touch()"
                          @blur="$v.editedItem.employee_code.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="last_name"
                          v-model="editedItem.last_name"
                          :error-messages="lastNameErrors"
                          label="Last Name"
                          @input="$v.editedItem.last_name.$touch()"
                          @blur="$v.editedItem.last_name.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="first_name"
                          v-model="editedItem.first_name"
                          :error-messages="firstNameErrors"
                          label="First Name"
                          @input="$v.editedItem.first_name.$touch()"
                          @blur="$v.editedItem.first_name.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="middle_name"
                          v-model="editedItem.middle_name"
                          label="Middle Name"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <!-- <v-row>
                    <v-col class="my-0 py-0">
                      <v-divider class="my-0 py-0"></v-divider>
                    </v-col>
                  </v-row> -->
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-menu
                          v-model="input_birth_date"
                          :close-on-content-click="false"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              name="birth_date"
                              v-model="computedBirthDateFormatted"
                              label="Birth Date (MM/DD/YYYY)"
                              persistent-hint
                              readonly
                              v-bind="attrs"
                              v-on="on"
                              :error-messages="birthdateErrors"
                              @input="$v.editedItem.birth_date.$touch()"
                              @blur="$v.editedItem.birth_date.$touch()"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="editedItem.birth_date"
                            no-title
                            @input="input_birth_date = false"
                          ></v-date-picker>
                        </v-menu>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-autocomplete
                          v-model="editedItem.gender"
                          :items="gender_items"
                          item-text="text"
                          item-value="value"
                          label="Gender"
                          :error-messages="genderErrors"
                          @input="$v.editedItem.gender.$touch()"
                          @blur="$v.editedItem.gender.$touch()"
                        >
                        </v-autocomplete>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-autocomplete
                          v-model="editedItem.civil_status"
                          :items="civil_status_items"
                          item-text="text"
                          item-value="value"
                          label="Civil Status"
                          :error-messages="civilStatusErrors"
                          @input="$v.editedItem.civil_status.$touch()"
                          @blur="$v.editedItem.civil_status.$touch()"
                        >
                        </v-autocomplete>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="contact"
                          v-model="editedItem.contact"
                          :error-messages="contactErrors"
                          label="Contact No."
                          @input="$v.editedItem.contact.$touch()"
                          @blur="$v.editedItem.contact.$touch()"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="email"
                          v-model="editedItem.email"
                          :error-messages="emailErrors"
                          label="E-mail"
                          @input="$v.editedItem.email.$touch()"
                          @blur="$v.editedItem.email.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="9" class="my-0 py-0">
                        <v-text-field
                          name="address"
                          v-model="editedItem.address"
                          :error-messages="addressErrors"
                          label="Address"
                          @input="$v.editedItem.address.$touch()"
                          @blur="$v.editedItem.address.$touch()"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col class="my-0 py-0">
                        <v-divider class="my-0 py-0"></v-divider>
                      </v-col>
                    </v-row>
                  </fieldset>
                  <fieldset>
                    <legend class="mb-6 mt-6">Employment Information</legend>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-autocomplete
                          v-model="editedItem.position"
                          :items="positions"
                          item-text="name"
                          item-value="id"
                          label="Job Description"
                          return-object
                          :error-messages="positionErrors"
                          @input="$v.editedItem.position.$touch()"
                          @blur="$v.editedItem.position.$touch()"
                        >
                        </v-autocomplete>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="rank"
                          v-model="editedItem.rank"
                          label="Rank"
                          readonly
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-autocomplete
                          v-model="editedItem.department"
                          :items="departments"
                          item-text="name"
                          item-value="id"
                          label="Department"
                          return-object
                          :error-messages="departmentErrors"
                          @input="$v.editedItem.department.$touch()"
                          @blur="$v.editedItem.department.$touch()"
                        >
                        </v-autocomplete>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="division"
                          v-model="editedItem.division"
                          label="Division"
                          readonly
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-autocomplete
                          v-model="editedItem.branch"
                          :items="branches"
                          item-text="name"
                          item-value="id"
                          label="Branch"
                          return-object
                          :error-messages="branchErrors"
                          @input="$v.editedItem.branch.$touch()"
                          @blur="$v.editedItem.branch.$touch()"
                        >
                        </v-autocomplete>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="company"
                          v-model="editedItem.company"
                          label="Company"
                          readonly
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-menu
                          v-model="input_date_employed"
                          :close-on-content-click="false"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              name="date_employed"
                              v-model="computedDateEmployedFormatted"
                              label="Date Employed (MM/DD/YYYY)"
                              persistent-hint
                              readonly
                              v-bind="attrs"
                              v-on="on"
                              :error-messages="dateEmployedErrors"
                              @input="$v.editedItem.date_employed.$touch()"
                              @blur="$v.editedItem.date_employed.$touch()"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="editedItem.date_employed"
                            no-title
                            @input="input_date_employed = false"
                          ></v-date-picker>
                        </v-menu>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-menu
                          v-model="input_date_resigned"
                          :close-on-content-click="false"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              name="date_resigned"
                              v-model="computedDateResignedFormatted"
                              label="Date Resigned (MM/DD/YYYY)"
                              persistent-hint
                              readonly
                              v-bind="attrs"
                              v-on="on"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="editedItem.date_resigned"
                            no-title
                            @input="input_date_resigned = false"
                          ></v-date-picker>
                        </v-menu>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="tin_no"
                          v-model="editedItem.tin_no"
                          :error-messages="tinNoErrors"
                          label="TIN No."
                          @input="$v.editedItem.tin_no.$touch()"
                          @blur="$v.editedItem.tin_no.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="pagibig_no"
                          v-model="editedItem.pagibig_no"
                          :error-messages="pagibigNoErrors"
                          label="PAG-IBIG No."
                          @input="$v.editedItem.pagibig_no.$touch()"
                          @blur="$v.editedItem.pagibig_no.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="philhealth_no"
                          v-model="editedItem.philhealth_no"
                          :error-messages="philhealthNoErrors"
                          label="PHILHEALTH No."
                          @input="$v.editedItem.philhealth_no.$touch()"
                          @blur="$v.editedItem.philhealth_no.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="sss_no"
                          v-model="editedItem.sss_no"
                          :error-messages="sssNoErrors"
                          label="SSS No."
                          @input="$v.editedItem.sss_no.$touch()"
                          @blur="$v.editedItem.sss_no.$touch()"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="length_of_service"
                          v-model="editedItem.length_of_service"
                          label="Length of Service"
                          readonly
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col class="my-0 py-0">
                        <v-divider class="my-0 py-0"></v-divider>
                      </v-col>
                    </v-row>
                  </fieldset>
                  <fieldset>
                    <legend class="mb-6 mt-6">Educational Background</legend>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="educ_attain"
                          v-model="editedItem.educ_attain"
                          :error-messages="educAttainErrors"
                          label="Educational Attainment"
                          @input="$v.editedItem.educ_attain.$touch()"
                          @blur="$v.editedItem.educ_attain.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="school_attended"
                          v-model="editedItem.school_attended"
                          :error-messages="schoolAttendedErrors"
                          label="School Attended"
                          @input="$v.editedItem.school_attended.$touch()"
                          @blur="$v.editedItem.school_attended.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="course"
                          v-model="editedItem.course"
                          :error-messages="courseErrors"
                          label="Course"
                          @input="$v.editedItem.course.$touch()"
                          @blur="$v.editedItem.course.$touch()"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col class="my-0 py-0">
                        <v-divider class="my-0 py-0"></v-divider>
                      </v-col>
                    </v-row>
                  </fieldset>
                  <fieldset>
                    <legend class="mb-6 mt-6">Status</legend>
                    <v-row>
                      <v-col cols="2" class="my-0 py-0">
                        <v-switch
                          v-model="editedItem.active"
                          :label="activeStatus"
                        ></v-switch>
                      </v-col>
                    </v-row>
                  </fieldset>
                </v-container>
              </v-card-text>
              <v-divider class="mb-3 mt-0"></v-divider>
              <v-card-actions class="pa-0">
                <v-spacer></v-spacer>
                <v-btn color="#E0E0E0" @click="close" class="mb-3">
                  Cancel
                </v-btn>
                <v-btn
                  color="primary"
                  @click="save"
                  :disabled="disabled"
                  class="mb-3 mr-4"
                >
                  Save
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <ImportDialog 
            :api_route="api_route" 
            :dialog_import="dialog_import"
            docname="Employee Master Data"
            @getData="getEmployee"
            @closeImportDialog="closeImportDialog"
          />
        </v-card>
      </v-main>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState, mapGetters } from "vuex";
import ImportDialog from "./components/ImportDialog.vue";
import MenuActions from "../components/MenuActions.vue";
import DataTable from "../components/DataTable.vue";
import moment from "moment";

export default {

  name: "EmployeeListView",
  components: {
    ImportDialog,
    MenuActions,
    DataTable,
  },
  props: {

  },
  mixins: [validationMixin],

  validations: {
    editedItem: {
      branch: { required },
      employee_code: { required },
      last_name: { required },
      first_name: { required },
      gender: { required },
      civil_status: { required },
      birth_date: { required },
      address: { required },
      contact: { required },
      email: { email },
      position: { required },
      department: { required },
      date_employed: { required },
      tin_no: { required },
      pagibig_no: { required },
      philhealth_no: { required },
      sss_no: { required },
      educ_attain: { required },
      school_attended: { required },
      course: { required },
    },
    
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "branch.name" },
        { text: "Company", value: "branch.company.name" },
        { text: "Emp. Code", value: "employee_code" },
        { text: "Lastname", value: "last_name" },
        { text: "Firstname", value: "first_name" },
        { text: "Middlename", value: "middle_name" },
        { text: "Birthday", value: "birth_date" },
        { text: "Address", value: "address" },
        { text: "Contact #", value: "contact" },
        { text: "Email", value: "email" },
        { text: "Job Description", value: "position.name" },
        { text: "Rank", value: "position.rank.name" },
        { text: "Department", value: "department.name" },
        { text: "Division", value: "department.division.name" },
        { text: "Date Employed", value: "date_employed" },
        { text: "Gender", value: "gender" },
        { text: "Civil Status", value: "civil_status" },
        { text: "TIN #", value: "tin_no" },
        { text: "Pag-IBIG #", value: "pagibig_no" },
        { text: "PhilHealth #", value: "philhealth_no" },
        { text: "SSS #", value: "sss_no" },
        { text: "Educ. Attainment", value: "educ_attain" },
        { text: "School Attended", value: "school_attended" },
        { text: "Course", value: "course" },
        { text: "Length of Service", value: "length_of_service" },
        { text: "Status", value: "active" },
        // { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      disabled: false,
      dialog: false,
      employees: [],
      branches: [],
      departments: [],
      positions: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Employee Lists",
          disabled: false,
          link: "/employee_master_data/list",
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
      branch: "",
      branch_id: "",
      editedIndex: -1,
      editedItem: {
        branch_id: "",
        branch: "",
        employee_code: "",
        last_name: "",
        first_name: "",
        birth_date: "",
        address: "",
        contact: "",
        email: "",
        position_id: "",
        position: "",
        department: "",
        department_id: "",
        rank: "",
        date_employed: "",
        date_signed: "",
        gender: "",
        civil_status: "",
        tin_no: "",
        pagibig_no: "",
        philhealth_no: "",
        sss_no: "",
        educ_attain: "",
        school_attended: "",
        course: "",
        length_of_service: "",
        active: true,
      },
      defaultItem: {
        branch_id: "",
        branch: "",
        employee_code: "",
        last_name: "",
        first_name: "",
        birth_date: "",
        address: "",
        contact: "",
        email: "",
        position_id: "",
        position: "",
        department: "",
        department_id: "",
        rank: "",
        date_employed: "",
        date_signed: "",
        gender: "",
        civil_status: "",
        tin_no: "",
        pagibig_no: "",
        philhealth_no: "",
        sss_no: "",
        educ_attain: "",
        school_attended: "",
        course: "",
        length_of_service: "",
        active: true,
      },
      input_birth_date: false,
      input_date_employed: false,
      input_date_resigned: false,
      dialog_import: false,
      api_route: "/api/employee_master_data/import", // api rout for uploading excel file
      swalAttr: {
        title: "",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "",
      },
      selectedTableHeaders: [],
      defaultTableHeaders: [
        { text: "Emp. Code", value: "employee_code" },
        { text: "Lastname", value: "last_name" },
        { text: "Firstname", value: "first_name" },
        { text: "Middlename", value: "middle_name" },
        { text: "Job Description", value: "position.name" },
        { text: "Branch", value: "branch.name" },
        { text: "Department", value: "department" },
        { text: "Date Employed", value: "date_employed" },
        { text: "Status", value: "active" },
      ],
    };
  },

  methods: {
    getEmployee() {

      this.loading = true;

      axios.get("/api/employee_master_data/index").then(
        (response) => {
          // if user has no permission to view overall list
          let data = response.data; 

          this.employees = data.employees;
          this.branches = data.branches;
          this.departments = data.departments;
          this.positions = data.positions;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
          console.log(error);
        }
      );
    },
    editEmployee(item) {

      this.editedItem.gender = item.gender.toUpperCase();
      this.editedItem.civil_status = item.civil_status.toUpperCase();

      Object.assign(item, { birth_date: item.dob });

      this.editedIndex = this.employees.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;

      // let [month, day, year] = this.editedItem.dob.split("/");
      // this.editedItem.birth_date = `${year}-${month}-${day}`;

      // [month, day, year] = this.editedItem.date_employed.split("/");
      // this.editedItem.date_employed = `${year}-${month}-${day}`;

      // [month, day, year] = this.editedItem.date_resigned.split("/");
      // this.editedItem.date_resigned = `${year}-${month}-${day}`;
    },

    deleteEmployee(employee_id) {
      const data = { employee_id: employee_id };

      axios.post("/api/employee_master_data/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "employee-master-data-delete" });
             this.showAlert(response.data.success, "success")
          }
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    save() {
      this.$v.editedItem.$touch();
      if (!this.$v.$error) {
        this.disabled = true;
        this.overlay = true;
        
        this.editedItem.branch_id = this.editedItem.branch.id;
        this.editedItem.department_id = this.editedItem.department.id;
        this.editedItem.position_id = this.editedItem.position.id;
        const data = this.editedItem;
        const employee_id = this.editedItem.id;
        let url = "/api/employee_master_data" + (this.editedIndex > -1 ? "/update/" + employee_id : "/store");

        axios.post(url, data).then(
          (response) => {
            this.overlay = false;
            this.disabled = false;
            let data = response.data;
     
            if (data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "employee-master-data-edit" });

              let employee = data.employee;
              
              // format date_granted
              // let [year, month, day] = employee.dob.split("-");
              // employee.birth_date = `${month}/${day}/${year}`;

              // [year, month, day] = employee.date_employed.split("-");
              // employee.date_employed = `${month}/${day}/${year}`;

              if(this.editedIndex > -1)
              {
                Object.assign(this.employees[this.editedIndex], employee );
              }
              else
              {
                this.employees.push(employee);
              }

              this.showAlert(data.success, "success");
              this.close();
            } else {
            }
          },
          (error) => {
            this.isUnauthorized(error);

            this.overlay = false;
            this.disabled = false;
          }
        );
      }
    },

    showAlert(title, icon) {
      this.$swal({
        position: "center",
        icon: icon,
        title: title,
        showConfirmButton: false,
        timer: 2500,
      });
    },

    showConfirmAlert(item) {
      Object.assign(this.swalAttr, { title: "Delete Record", confirmButtonText: "Delete Record!" });

      this.$swal(this.swalAttr).then(async (result) => {
        // <--

        if (result.value) {
          // <-- if confirmed

          const employee_id = item.id;
          const index = this.employees.indexOf(item);

          //Call delete User function
          this.deleteEmployee(employee_id);

          //Remove item from array services
          this.employees.splice(index, 1);
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

    closeImportDialog() {
      this.dialog_import = false;
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
    },

    exportData() {
      if (this.employees.length) {
        axios.get('/api/employee_master_data/export', { responseType: 'arraybuffer'})
          .then((response) => {
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement('a');
              fileLink.href = fileURL;
              fileLink.setAttribute('download', 'EmployeeList.xls');
              document.body.appendChild(fileLink);
              fileLink.click();
          }, (error) => {
            console.log(error);
          }
        );
      } else {
        this.showAlert("No record found", "warning")
      }
    },
    formatDate(date) {
      if (!date) return null;
      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },
    removeSelectedHeader(item) {
      let index = this.selectedTableHeaders.findIndex(header => header.value === item.value);
      this.selectedTableHeaders.splice(index, 1);
    },
    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;

        if (
          action == "employee-master-data-create" ||
          action == "employee-master-data-edit" ||
          action == "employee-master-data-delete" ||
          action == "employee-master-data-import"
        ) {
          this.getEmployee();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Employee" : "Edit Employee";
    },
    branchErrors() {
      const errors = [];
      if (!this.$v.editedItem.branch.$dirty) return errors;
      !this.$v.editedItem.branch.required &&
        errors.push("Branch is required.");
      return errors;
    },
    employeeCodeErrors() {
      const errors = [];
      if (!this.$v.editedItem.employee_code.$dirty) return errors;
      !this.$v.editedItem.employee_code.required &&
        errors.push("Employee Code is required.");
      return errors;
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
    birthdateErrors() {
      const errors = [];
      if (!this.$v.editedItem.birth_date.$dirty) return errors;
      !this.$v.editedItem.birth_date.required && errors.push("Birthdate is required.");
      return errors;
    },
    computedBirthDateFormatted() {
      return this.formatDate(this.editedItem.birth_date);
    },
    addressErrors() {
      const errors = [];
      if (!this.$v.editedItem.address.$dirty) return errors;
      !this.$v.editedItem.address.required &&
        errors.push("Address is required.");
      return errors;
    },
    contactErrors() {
      const errors = [];
      if (!this.$v.editedItem.contact.$dirty) return errors;
      !this.$v.editedItem.contact.required &&
        errors.push("Contact is required.");
      return errors;
    },
    emailErrors() {
      const errors = [];
      if (!this.$v.editedItem.email.$dirty) return errors;
      // !this.$v.editedItem.email.required && errors.push("Email is required.");
      !this.$v.editedItem.email.email && errors.push("Must be valid e-mail");
      return errors;
    },
    positionErrors() {
      const errors = [];
      if (!this.$v.editedItem.position.$dirty) return errors;
      !this.$v.editedItem.position.required && errors.push("Job Description is required.");
      return errors;
    },
    departmentErrors() {
      const errors = [];
      if (!this.$v.editedItem.department.$dirty) return errors;
      !this.$v.editedItem.department.required &&
        errors.push("Department is required.");
      return errors;
    },
    contactErrors() {
      const errors = [];
      if (!this.$v.editedItem.contact.$dirty) return errors;
      !this.$v.editedItem.contact.required &&
        errors.push("Contact is required.");
      return errors;
    },
    dateEmployedErrors() {
      const errors = [];
      if (!this.$v.editedItem.date_employed.$dirty) return errors;
      !this.$v.editedItem.date_employed.required &&
        errors.push("Date Employed is required.");
      return errors;
    },
    computedDateEmployedFormatted() {
      return this.formatDate(this.editedItem.date_employed);
    },
    computedDateResignedFormatted() {
      return this.formatDate(this.editedItem.date_resigned);
    },
    contactErrors() {
      const errors = [];
      if (!this.$v.editedItem.contact.$dirty) return errors;
      !this.$v.editedItem.contact.required &&
        errors.push("Contact is required.");
      return errors;
    },
    genderErrors() {
      const errors = [];
      if (!this.$v.editedItem.gender.$dirty) return errors;
      !this.$v.editedItem.gender.required && errors.push("Gender is required.");
      return errors;
    },
    civilStatusErrors() {
      const errors = [];
      if (!this.$v.editedItem.civil_status.$dirty) return errors;
      !this.$v.editedItem.civil_status.required &&
        errors.push("Civil Status is required.");
      return errors;
    },
    tinNoErrors() {
      const errors = [];
      if (!this.$v.editedItem.tin_no.$dirty) return errors;
      !this.$v.editedItem.tin_no.required &&
        errors.push("TIN No. is required.");
      return errors;
    },
    pagibigNoErrors() {
      const errors = [];
      if (!this.$v.editedItem.pagibig_no.$dirty) return errors;
      !this.$v.editedItem.pagibig_no.required &&
        errors.push("PagIbig No. is required.");
      return errors;
    },
    philhealthNoErrors() {
      const errors = [];
      if (!this.$v.editedItem.philhealth_no.$dirty) return errors;
      !this.$v.editedItem.philhealth_no.required &&
        errors.push("Philhealth No. is required.");
      return errors;
    },
    sssNoErrors() {
      const errors = [];
      if (!this.$v.editedItem.sss_no.$dirty) return errors;
      !this.$v.editedItem.sss_no.required &&
        errors.push("SSS No. is required.");
      return errors;
    },
    educAttainErrors() {
      const errors = [];
      if (!this.$v.editedItem.educ_attain.$dirty) return errors;
      !this.$v.editedItem.educ_attain.required &&
        errors.push("Educ. Attainment is required.");
      return errors;
    },
    schoolAttendedErrors() {
      const errors = [];
      if (!this.$v.editedItem.school_attended.$dirty) return errors;
      !this.$v.editedItem.school_attended.required &&
        errors.push("School Attended is required.");
      return errors;
    },
    courseErrors() {
      const errors = [];
      if (!this.$v.editedItem.course.$dirty) return errors;
      !this.$v.editedItem.course.required && errors.push("Course is required.");
      return errors;
    },
    tableHeaders() {
      
      let headers = [];
      
      this.selectedTableHeaders.forEach(value => {
        this.headers.forEach(header => {
          if(header.value == value.value)
          {
            headers.push(header);
          }
        }); 
      });

      headers.push({ text: "Actions", value: "actions", sortable: false, width: "80px" })
 
      return headers;
    },
    headerItems() {
      
      let headers = [];
      let selected_items = this.selectedTableHeaders;
      
      this.headers.forEach(header => {
    
        let disable = false;
        
        // if selectedTableHeaders has 1 item then disable item -- must be atleast 1 column for data datable
        if(selected_items.length == 1 && selected_items[0].value == header.value)
        { 
          disable = true;
          headers.push(Object.assign(header, { disable: disable }));
        }
        //disable all unselected items when selected items is equal to 9
        else if(selected_items.length == 9)
        { 
          let index = selected_items.findIndex(item => item.value === header.value);  
          disable = index > -1 ? false : true;
        } 
        
        headers.push(Object.assign(header, { disable: disable }));
      });
      
      return headers;
    },
    activeStatus() {
      return this.editedItem.active ? 'Active' : 'Inactive';
    },
    ...mapState("auth", ["user", "userIsLoaded"]),
    ...mapState("userRolesPermissions", ["userRolesPermissionsIsLoaded"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),
  },

  watch: {
    'editedItem.department'() { 
      let division = this.editedItem.department.division;
      this.editedItem.division = division ? division.name : '';
    },
    'editedItem.branch'() {
      let company = this.editedItem.branch.company;
      this.editedItem.company = company ? company.name : '';
    },
    'editedItem.position'() {
      let rank = this.editedItem.position.rank;
      // console.log(this.editedItem.position);
      this.editedItem.rank = rank ? rank.name : '';
    },
    'editedItem.date_employed'() {
      // var date_resigned = this.editedItem.date_resigned ? moment(this.editedItem.date_resigned) : moment();//now
      // var date_employed = moment(new Date(this.editedItem.date_employed));

      // console.log(date_resigned.diff(date_employed, 'years')) // 44700
      // console.log(date_resigned.diff(date_employed, 'months')) // 745
      // console.log(date_resigned.diff(date_employed, 'days')) // 31

    },

  }, 

  mounted() {
    
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");

    this.defaultTableHeaders.forEach(value => {
      this.selectedTableHeaders.push(value)
    });

    this.getEmployee();
  },
};
</script>