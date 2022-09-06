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
            <span class="headline">Edit Marketing Event</span>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-6">
            <v-row>
              <v-col cols="4" class="mt-0 mb-0 pt-0 pb-0">
                <v-text-field
                  name="event_name"
                  v-model="editedItem.event_name"
                  label="Event Title"
                  :error-messages="eventErrors + eventError.event_name"
                  @input="$v.editedItem.event_name.$touch() + (eventError.event_name = [])"
                  @blur="$v.editedItem.event_name.$touch()"
                >
                  ></v-text-field
                >
              </v-col>
            </v-row>
            <v-row>
              <v-col>
                <fieldset class="border-1">
                  <legend>Expense Particulars:</legend>
                  <v-treeview
                    v-model="tree"
                    :items="expense_particulars"
                    open-all
                    hoverable
                  >
                    <template v-slot:prepend="{ item, open }">
                      <v-btn
                        color="primary"
                        @click="addSubItem(item)"
                        icon
                        v-if="item.children"
                      >
                        <v-icon>mdi-plus-circle</v-icon>
                      </v-btn>
                      <v-btn
                        color="red"
                        @click="showConfirmAlert(item)"
                        icon
                        :class="!item.children ? 'ml-12' : ''"
                      >
                        <v-icon class="ma-0 pa-0">mdi-minus-circle</v-icon>
                      </v-btn>
                    </template>
                    <template v-slot:label="{ item, open }">
                      <v-text-field
                        name="description"
                        v-model="item.description"
                        dense
                        hide-details
                        outlined
                        :error-messages="
                          !item.description && item.hasError ? 'error' : ''
                        "
                      ></v-text-field>
                    </template>
                  </v-treeview>
                </fieldset>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="text-right">
                <v-btn color="primary" class="mr-2" @click="addItem()" small>
                  add item
                </v-btn>
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
              Update
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

export default {
  mixins: [validationMixin],

  validations: {
    editedItem: {
      event_name: { required },
    },
  },
  data() {
    return {
      open_all: false,
      open: [],
      tree: [],
      absolute: true,
      overlay: false,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Marketing Event Lists",
          disabled: false,
          link: "/marketing_event/index",
        },
        {
          text: "Edit Marketing Event",
          disabled: true,
        },
      ],
      disabled: false,

      editedItem: {
        event_name: "",
      },
      defaultItem: {
        event_name: "",
      },
      initiallyOpen: [""],
      expense_particulars: [],
      errorFields: [],
      tree_view: [],
      deletedRows: [],
      addedRows: [],
      eventError: {
        event_name: [],
      }
    };
  },

  methods: {
    getMarketingEvent() {
      const data = {
        marketing_event_id: this.$route.params.marketing_event_id,
      };

      axios.post("/api/marketing_event/edit", data).then(
        (response) => {
          this.marketing_event = response.data.marketing_event;
          this.editedItem.event_name = this.marketing_event.event_name;
          let expense_particulars = this.marketing_event.expense_particulars;

          this.expense_particulars = [];

          expense_particulars.forEach((value, index) => {
            this.expense_particulars.push({
              id: value.id,
              description: value.description,
              children: [],
              hasError: false,
            });

            let sub_items = value.expense_sub_particulars;

            if (sub_items.length) {
              sub_items.forEach((val, i) => {
                this.expense_particulars[index].children.push({
                  id: val.id,
                  parent_index: index,
                  description: val.description,
                  hasError: false,
                });
              });
            }
          });
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

    save() {
      this.$v.$touch();
      let hasError = this.validateExpenseParticulars();
      this.eventError = {
        event_name: []
      };
      
      if (!this.$v.$error && !hasError) {
        this.disabled = true;
        this.overlay = true;

        this.editedItem["expense_particulars"] = this.expense_particulars;

        const marketing_event_id = this.$route.params.marketing_event_id;

        const data = {
          event_name: this.editedItem.event_name,
          expense_particulars: this.expense_particulars,
          deletedRows: this.deletedRows,
        };

        axios
          .post("/api/marketing_event/update/" + marketing_event_id, data)
          .then(
            (response) => {

              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "marketing-event-create" });
                this.getMarketingEvent();
                this.showAlert();
              } else {
                  let errors = response.data;
                  let errorNames = Object.keys(response.data);
                  errorNames.forEach(value => {
                    this.eventError[value].push(errors[value]);
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
    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.expense_particulars = [
        { description: "", children: [], hasError: false },
      ];
      this.eventError = {
        event_name: []
      }
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },
    addItem() {
      this.expense_particulars.push({
        description: "",
        children: [],
        hasError: false,
      });
    },
    addSubItem(item) {
      let index = this.expense_particulars.indexOf(item);
      let expense_particular = this.expense_particulars[index];

      // if current index has value then push data
      if (item.description) {
        expense_particular.children.push({
          parent_index: index,
          description: "",
          hasError: false,
        });
      }
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
        if (result.value) {
          this.removeItem(item);
        }
      });
    },

    removeItem(item) {
      let index = this.expense_particulars.indexOf(item);
      let object_names = Object.keys(item);
      let hasChildren = false;
      let deletedRow = "";
      let ctr = this.expense_particulars.length;

      object_names.forEach((value) => {
        if (value === "children") {
          hasChildren = true;
        }
      });

      // if data has children
      if (hasChildren) {
        if (ctr > 1) {
          this.expense_particulars.splice(index, 1);
          deletedRow = "expense_particulars";
        }
      } else {
        let child_index =
          this.expense_particulars[item.parent_index].children.indexOf(item);

        this.expense_particulars[item.parent_index].children.splice(
          child_index,
          1
        );
        deletedRow = "expense_sub_particulars";
      }

      if (item.id) {
        this.deletedRows.push({ deletedRow: deletedRow, id: item.id });
      }

      // refresh/update parent_index field for child data
      this.expense_particulars.forEach((value, index) => {
        value.children.forEach((val, i) => {
          this.expense_particulars[index].children[i].parent_index = index;
        });
      });
    },
    validateExpenseParticulars() {
      let hasError = false;
      this.expense_particulars.forEach((value, index) => {
        hasError = false;

        if (!value.description) {
          hasError = true;
        }
        this.expense_particulars[index].hasError = hasError;

        value.children.forEach((val, i) => {
          hasError = false;
          if (!val.description) {
            hasError = true;
          }
          this.expense_particulars[index].children[i].hasError = true;
        });
      });

      return hasError;
    },
  },
  computed: {
    eventErrors() {
      const errors = [];
      if (!this.$v.editedItem.event_name.$dirty) return errors;
      !this.$v.editedItem.event_name.required &&
        errors.push("Event Title is required.");
      return errors;
    },
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getMarketingEvent();
  },
};
</script>