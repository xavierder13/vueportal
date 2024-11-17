<template>
  <div>
    <v-tabs v-model="tab" background-color="blue darken-1" dark class="ma-0" style="border-radius: 5px;">
      <v-tab class="tab-menu" :style="tab == 0 ? tabCSS : ''">
        Personal Data
      </v-tab>
      <v-tab class="tab-menu" :style="tab == 1 ? tabCSS : ''">
        Employee Details
      </v-tab>
      <v-tab class="tab-menu" :style="tab == 2 ? tabCSS : ''">
        Performance Management
      </v-tab>
      <v-tab class="tab-menu" :style="tab == 3 ? tabCSS : ''">
        Disciplinary Measures & Penalties
      </v-tab>
      <v-tab class="tab-menu" :style="tab == 4 ? tabCSS : ''">
        Offboarding
      </v-tab>
    </v-tabs>
    <v-tabs-items v-model="tab">
      <v-tab-item :transition="false" class="full-height-tab-main py-2">
        <v-card class="mx-2" height="100%">
          <v-card-text>
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
            <v-row>
              <v-col cols="3" class="my-0 py-0">
                <v-text-field
                  label="Birth Date"
                  type="date"
                  prepend-icon="mdi-calendar"
                  v-model="editedItem.birth_date"
                  :error-messages="birthdateErrors"
                  @input="$v.editedItem.birth_date.$touch() + validateDate('birth_date')"
                  @blur="$v.editedItem.birth_date.$touch()"
                ></v-text-field>
              </v-col>
              <v-col cols="3" class="my-0 py-0">
                <v-text-field
                  name="age"
                  v-model="age"
                  label="Age"
                  readonly
                ></v-text-field>
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
            </v-row>
            <v-row>
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
              <v-col cols="6" class="my-0 py-0">
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
            </v-row>
            <v-row>
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
          </v-card-text>
        </v-card>
      </v-tab-item>
      <v-tab-item :transition="false" class="full-height-tab-main py-2">
        <v-card class="mx-2" height="100%">
          <v-card-text>
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
                <v-text-field
                  label="Date Employed"
                  type="date"
                  prepend-icon="mdi-calendar"
                  v-model="editedItem.date_employed"
                  :error-messages="dateEmployedErrors"
                  @input="$v.editedItem.date_employed.$touch() + validateDate('date_employed')"
                  @blur="$v.editedItem.date_employed.$touch()"
                ></v-text-field>
              </v-col>
              <v-col cols="3" class="my-0 py-0">
                <v-text-field
                  label="Date Resigned"
                  type="date"
                  prepend-icon="mdi-calendar"
                  v-model="editedItem.date_resigned"
                  :error-messages="dateResignedErrors"
                  @input="validateDate('date_resigned')"
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
              <v-col cols="3" class="my-0 py-0">
                <v-autocomplete
                  v-model="editedItem.cost_center"
                  :items="cost_centers"
                  label="Cost Center"
                >
                </v-autocomplete>
              </v-col>
              <v-col cols="3" class="my-0 py-0">
                <v-autocomplete
                  v-model="editedItem.employment_type"
                  :items="employment_types"
                  label="Employment Types"
                >
                </v-autocomplete>
              </v-col>
              <v-col cols="1" class="my-0 py-0">
                <v-switch
                  v-model="editedItem.active"
                  hide-details
                  inset
                >
                  <template v-slot:label>
                    <v-chip :color="editedItem.active ? 'success' : ''" class="mt-1">{{activeStatus}}</v-chip>
                  </template>
                </v-switch>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-tab-item>
      <v-tab-item :transition="false" class="full-height-tab-main">
        <v-tabs vertical color="primary" light class="pa-0 ma-0">
          <v-tab class="performance-tab-menu mt-2">
            Evaluation & Regularization
          </v-tab>
          <v-tab class="performance-tab-menu">
            Monthly Key Performance
          </v-tab>
          <v-tab class="performance-tab-menu">
            Performance Rating During Training
          </v-tab>
          <v-tab class="performance-tab-menu">
            Branch Assignment & Positions
          </v-tab>
          <v-tab class="performance-tab-menu">
            Merit History
          </v-tab>
          <v-tab class="performance-tab-menu">
            Traing and Re-development
          </v-tab>
          <v-tab-item :transition="false" class="full-height-tab-performance py-2">
            <v-card class="mx-2" height="100%">
              <v-card-text>
                <v-row>
                  <v-col class="my-0 py-0">
                    <span class="subtitle-1">Evaluation of Performance for Regularization (attachment): </span>
                    <template v-if="regularization_file">
                      <span> <a href="http://">asdsadasdasads.pdf</a> </span>
                      <v-tooltip top>
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn 
                            x-small
                            color="error" 
                            rounded
                            icon
                            @click="showConfirmAlert()"
                            v-bind="attrs" 
                            v-on="on"
                          > 
                            <v-icon>mdi-delete</v-icon> 
                          </v-btn>
                        </template>
                        <span>Delete File</span>
                      </v-tooltip> 
                    </template>
                    
                  </v-col>
                </v-row>
                <v-row v-if="!regularization_memo_file">
                  <v-col cols="4" class="my-0 py-0">
                    <v-file-input
                      v-model="regularization_file_input"
                      show-size
                      label="Attach File"
                      prepend-icon="mdi-paperclip"
                      required
                      multiple
                      :error-messages="regularizationFileErrors"
                      @change="validateFile()"
                      clearable
                    >
                    </v-file-input>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col class="my-0 py-0">
                    <span>Memo of Regularization (attachment): </span>
                    <template v-if="regularization_memo_file">
                      <span> <a href="http://">asdsadasdasads.pdf</a> </span>
                      <v-tooltip top>
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn 
                            x-small
                            color="error" 
                            rounded
                            icon
                            @click="showConfirmAlert()"
                            v-bind="attrs" 
                            v-on="on"
                          > 
                            <v-icon>mdi-delete</v-icon> 
                          </v-btn>
                        </template>
                        <span>Delete File</span>
                      </v-tooltip> 
                    </template>
                  </v-col>
                </v-row>
                <v-row v-if="!regularization_memo_file">
                  <v-col cols="4" class="my-0 py-0">
                    <v-file-input
                      v-model="regularization_memo_file_input"
                      show-size
                      label="Attach File"
                      prepend-icon="mdi-paperclip"
                      required
                      multiple
                      :error-messages="regularizationMemoFileErrors"
                      @change="validateFile()"
                      clearable
                    >
                    </v-file-input>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="4" class="my-0 py-0">
                    <v-text-field
                      label="Date of Regularization"
                      type="date"
                      prepend-icon="mdi-calendar"
                      v-model="editedItem.date_regularization"
                      :error-messages="dateRegularizationErrors"
                      @input="$v.editedItem.date_regularization.$touch() + validateDate('date_regularization')"
                      @blur="$v.editedItem.date_regularization.$touch()"
                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item :transition="false" class="full-height-tab-performance py-2">
            <KeyPerformanceTable/>
          </v-tab-item>
          <v-tab-item :transition="false" class="full-height-tab-performance py-2">
            <v-card class="mx-2" height="100%">
              <v-card-text>
                <span>Evaluation of Performance for Regularization (attachment): </span>
                <template v-if="regularization_file">
                  <span> <a href="http://">asdsadasdasads.pdf</a> </span>
                </template>
                <v-btn v-if="!regularization_file" @click="openAttachFileDialog()">Upload File</v-btn>
              </v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item :transition="false" class="full-height-tab-performance py-2">
            <v-card class="mx-2" height="100%">
              <v-card-text>
                <span>Evaluation of Performance for Regularization (attachment): </span>
                <template v-if="regularization_file">
                  <span> <a href="http://">asdsadasdasads.pdf</a> </span>
                </template>
                <v-btn v-if="!regularization_file" @click="openAttachFileDialog()">Upload File</v-btn>
              </v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item :transition="false" class="full-height-tab-performance py-2">
            <v-card class="mx-2" height="100%">
              <v-card-text>
                <span>Evaluation of Performance for Regularization (attachment): </span>
                <template v-if="regularization_file">
                  <span> <a href="http://">asdsadasdasads.pdf</a> </span>
                </template>
                <v-btn v-if="!regularization_file" @click="openAttachFileDialog()">Upload File</v-btn>
              </v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item :transition="false" class="full-height-tab-performance py-2">
            <v-card class="mx-2" height="100%">
              <v-card-text>
                <span>Evaluation of Performance for Regularization (attachment): </span>
                <template v-if="regularization_file">
                  <span> <a href="http://">asdsadasdasads.pdf</a> </span>
                </template>
                <v-btn v-if="!regularization_file" @click="openAttachFileDialog()">Upload File</v-btn>
              </v-card-text>
            </v-card>
          </v-tab-item>
        </v-tabs>
      </v-tab-item>
      <v-tab-item :transition="false" >
        <fieldset>
          <legend class="mb-6 mt-6 title">Disciplinary Measures & Penalties</legend>
        </fieldset>
      </v-tab-item>
      <v-tab-item :transition="false" >
        <fieldset>
          <legend class="mb-6 mt-6 title">Offboarding</legend>
        </fieldset>
      </v-tab-item>
    </v-tabs-items>  
  </div>
    
</template>
<style scoped>
  .full-height-tab-main {
    height: calc(85vh - 200px); /* Adjust 270px to suits your needs */
    overflow-y: auto;
    overflow-x: hidden;
  }
  .tab-menu {
    letter-spacing: normal !important;
    display: flex !important;
    flex-direction: row !important;
    justify-content: center !important;
    align-items: center !important;
    border: 1px solid #f7f7f7;
    border-radius: 10px;
    margin: 13px 13px;
  }
  .full-height-tab-performance {
    height: calc(85vh - 200px); /* Adjust 270px to suits your needs */
    overflow-y: auto;
    overflow-x: hidden;
  }
  .performance-tab-menu {
    letter-spacing: normal !important;
    display: flex !important;
    flex-direction: row !important;
    justify-content: center !important;
    align-items: center !important;
    border: 1px solid #1e88e5;
    border-radius: 5px;
    margin: 2px 2px;
  }
</style>
<script>
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import KeyPerformanceTable from './performance_component/KeyPerformanceTable.vue';

export default {
  components: {
    KeyPerformanceTable,
  },
  props: [
    'data',
    'branches',
    'positions',
    'departments'
  ],
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
      tab: 0,
      tab_performance: 0,
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
        cost_center: "",
        employment_type: "",
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
        cost_center: "",
        employment_type: "",
        active: true,
      },
      dateErrors: {
        birth_date: { status: false, msg: "" },
        date_employed: { status: false, msg: "" },
        date_resigned: { status: false, msg: "" },
        date_regularization: { status: false, msg: "" },
      },
      input_birth_date: false,
      input_date_employed: false,
      input_date_resigned: false,
      cost_centers: ['HQ-Management', 'BR-Officer', 'BR-Rank & Files',],
      employment_types: ['Regular', 'Probitionary'],
      regularization_file_input: [],
      regularization_memo_file_input: [],
      regularization_file: "",
      regularization_memo_file: "",
      fileInvalid: false,
    }
  },
  methods: {
    openAttachFileDialog() {
      this.$emit('openAttachFileDialog');
    },
    formatDate(date) {
      if (!date) return null;
      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },
    validateDate(field) {
      
      // if field is set for validation
      if(this.$v.editedItem[field])
      {
        this.$v.editedItem[field].$touch();
      }

      let min_date = new Date('1900-01-01').getTime();
      let max_date = new Date().getTime();
      let date = this.editedItem[field];
   
      if(date)
      {
        let date_value = new Date(date).getTime();
        let [year, month, day] = date.split('-');

        this.dateErrors[field].status = false;
        this.dateErrors[field].msg = "";

        // if (date_value < min_date || date_value > max_date || year.length > 4) {
        if (date_value > max_date || year.length > 4 || year < 1900) {
          this.dateErrors[field].status = true;
          this.dateErrors[field].msg = 'Enter a valid date';
        }  

        // if field is date_employed or date_resigned then validate, date resigned must be greater than date_employed
        if(['date_employed', 'date_resigned'].includes(field))
        {
          
          // if both date_employed and date_resigned have value
          if(this.editedItem.date_employed && this.editedItem.date_resigned)
          {

            let date_employed = new Date(this.editedItem.date_employed).getTime();
            let date_resigned = new Date(this.editedItem.date_resigned).getTime();

            this.dateErrors['date_employed'].status = false;
            this.dateErrors['date_employed'].msg = '';

            this.dateErrors['date_resigned'].status = false;
            this.dateErrors['date_resigned'].msg = '';

            if (date_employed > date_resigned) {
              this.dateErrors['date_employed'].status = true;
              this.dateErrors['date_employed'].msg = 'Enter a valid date';

              this.dateErrors['date_resigned'].status = true;
              this.dateErrors['date_resigned'].msg = 'Enter a valid date';
            } 
          }
        }

      }
  
    },
    validateFile(){
      let myFileInput = this.applicant.myFileInput;
      let copy_of_grades = this.applicant.copy_of_grades;

      let extensions = ['docs', 'docx', 'pdf', 'jpg', 'jpeg', 'png'];
    
      if(myFileInput)
      {
        if(myFileInput.name)
        {
          let split_arr = myFileInput.name.split('.');
          let split_ctr = split_arr.length;
          let extension = split_arr[split_ctr - 1].toLowerCase();
          
          if(!extensions.includes(extension))
          {
            this.fileInvalid = true;
          }

          if(myFileInput.size > 5000000) // 5000000 bytes or 20MB
          {
            this.fileInvalid = true;
          }
        }
      }

      if(copy_of_grades.length)
      {
        copy_of_grades.forEach(file => {
          if(file.name)
          {
            let split_arr = file.name.split('.');
            let split_ctr = split_arr.length;
            let extension = split_arr[split_ctr - 1].toLowerCase();
            
            if(!extensions.includes(extension))
            {
              this.fileInvalid = true;
            }

            if(file.size > 5000000) // 5000000 bytes or 20MB
            {
              this.fileInvalid = true;
            }
          }
        });
        
      }

      this.continue_2 = true;

      if(!myFileInput || myFileInput.length == 0 || !copy_of_grades.length || this.fileInvalid){
        this.continue_2 = false;
      }
      
    },
    showConfirmAlert(item, action) {

      Object.assign(this.swalAttr, { title: "Delete Record", confirmButtonText: "Delete Record!" });
      let data = { employee_id: item.id } 

      if(action == 'Multiple Delete')
      {
        Object.assign(this.swalAttr, { 
          title: "Delete Multiple Record", 
          confirmButtonText: "Delete Record",
          confirmButtonColor: "#d33", 
        });

        data = { employee_id: item.map(value => value.id) };
        
      }

      this.$swal(this.swalAttr).then(async (result) => {
        // <--

        if (result.value) {
          // <-- if confirmed

          // const employee_id = item.id;
          // const index = this.employees.indexOf(item);

          //Call delete User function
          this.deleteEmployee(data);

          // //Remove item from array services
          // this.employees.splice(index, 1);
        }
      });
    },
  },
  watch: {
    editedIndex() {
      console.log(this.editedIndex);
      
      if(this.editedIndex > -1)
      {

      }
    },
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
  computed: {
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

      if(this.dateErrors.birth_date.msg)
      {
        errors.push(this.dateErrors.birth_date.msg);
      }

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

      if(this.dateErrors.date_employed.msg)
      {
        errors.push(this.dateErrors.date_employed.msg);
      }
      return errors;
    },
    dateResignedErrors() {
      const errors = [];

      if(this.dateErrors.date_resigned.msg)
      {
        errors.push(this.dateErrors.date_resigned.msg);
      }
      return errors;
    },
    dateRegularizationErrors() {
      const errors = [];

      if(this.dateErrors.date_regularization.msg)
      {
        errors.push(this.dateErrors.date_regularization.msg);
      }
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
    age() {
      return "";
    },
    regularizationFileErrors() {
      
      const errors = [];

      let file = this.regularization_file_input;
      let extensions = ['docs', 'docx', 'pdf', 'jpg', 'jpeg', 'png'];
      let errorMsg = "";
      let fileInvalid = false;
    
      if(file)
      {
        if(file.name)
        {
          let split_arr = file.name.split('.');
          let split_ctr = split_arr.length;
          let extension = split_arr[split_ctr - 1].toLowerCase();
          
          if(!extensions.includes(extension))
          {
            fileInvalid = true;
            errorMsg = `File type must be ${extensions.join(', ')}.`;
          }

          if(file.size > 5000000) // 5000000 bytes or 20MB
          {
            errorMsg = "File size maximum is 5MB";
            fileInvalid = true;
          }
        }
      }
      this.fileInvalid = fileInvalid;
      fileInvalid && errors.push(errorMsg);

      return errors
        
    },
    regularizationMemoFileErrors() {
      
      const errors = [];

      let file = this.regularization_memo_file_input;
      let extensions = ['docs', 'docx', 'pdf', 'jpg', 'jpeg', 'png'];
      let errorMsg = "";
      let fileInvalid = false;
    
      if(file)
      {
        if(file.name)
        {
          let split_arr = file.name.split('.');
          let split_ctr = split_arr.length;
          let extension = split_arr[split_ctr - 1].toLowerCase();
          
          if(!extensions.includes(extension))
          {
            fileInvalid = true;
            errorMsg = `File type must be ${extensions.join(', ')}.`;
          }

          if(file.size > 5000000) // 5000000 bytes or 20MB
          {
            errorMsg = "File size maximum is 5MB";
            fileInvalid = true;
          }
        }
      }
      this.fileInvalid = fileInvalid;
      fileInvalid && errors.push(errorMsg);

      return errors
        
    },
    activeStatus() {
      return this.editedItem.active ? 'Active' : 'Inactive';
    },
    tabCSS() {
      return "color: #1E88E5 !important; background-color: white !important;";
    }
  }
}
</script>
