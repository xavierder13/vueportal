<template>
  <v-app>
    <!-- Navbar -->
    <v-app-bar dense dark app>
      <v-btn icon @click.stop="drawer = !drawer">
        <v-app-bar-nav-icon></v-app-bar-nav-icon>
      </v-btn>
      <v-spacer></v-spacer>
      <v-menu offset-y :nudge-width="200">
        <template v-slot:activator="{ on, attrs }">
          <v-btn small rounded v-bind="attrs" v-on="on" color="grey darken-3">
            <v-icon> mdi-menu-down </v-icon>
          </v-btn>
        </template>
        <v-card color="grey lighten-3">
          <v-card-text class="text-center">
            <v-row>
              <v-col
                ><img
                  src="/img/default-profile.png"
                  width="120px"
                  height="100px"
                  alt="User"
                  style="border-radius: 50%"
              /></v-col>
            </v-row>
            <v-row>
              <v-col>
                <h5 class="text--secondary">
                  {{ user.name }}
                </h5>
                <h6 class="text--disabled">
                  {{
                    user.id === 1
                      ? "Administrator"
                      : user.position
                      ? user.position.name
                      : ""
                  }}
                </h6>
              </v-col>
            </v-row>
          </v-card-text>
          <v-divider class="pa-0 mb-0"></v-divider>
          <v-card-actions class="grey darken-2 d-flex justify-content-around">
            <div>
              <v-btn class="white--text" plain small @click="userProfile()">
                <v-icon small class="mr-1">mdi-account</v-icon> Profile
              </v-btn>
            </div>
            <div>
              <v-btn class="white--text" plain small @click="logout">
                <v-icon small class="mr-1">mdi-logout</v-icon> Logout
              </v-btn>
            </div>
          </v-card-actions>
        </v-card>
      </v-menu>
    </v-app-bar>

    <!-- Sidebar -->
    <v-navigation-drawer v-model="drawer" dark app>
      <v-list>
        <v-list-item class="px-2">
          <v-list-item-avatar class="rounded-5" height="60" width="60">
            <v-img src="/img/addessa.jpg"></v-img>
          </v-list-item-avatar>
          <v-list-item-content>
            <v-list-item-title>Web Portal</v-list-item-title>
            <v-list-item-subtitle>{{ user.name }}</v-list-item-subtitle>
          </v-list-item-content>
        </v-list-item>
      </v-list>

      <v-divider></v-divider>

      <v-list>
        <!-- <v-list-item link to="/dashboard">
          <v-list-item-icon>
            <v-icon>mdi-view-dashboard</v-icon>
          </v-list-item-icon>
          <v-list-item-title>Dashboard</v-list-item-title>
        </v-list-item> -->
        <v-list-group
          no-action
          v-if="userPermissions.user_list || userPermissions.user_create"
        >
          <!-- List Group Icon-->
          <v-icon slot="prependIcon">mdi-account-arrow-right-outline</v-icon>
          <!-- List Group Title -->
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>User</v-list-item-title>
            </v-list-item-content>
          </template>
          <!-- List Group Items -->
          <v-list-item link to="/user/index" v-if="userPermissions.user_list">
            <v-list-item-content>
              <v-list-item-title>User Record</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/user/create"
            v-if="userPermissions.user_create"
          >
            <v-list-item-content>
              <v-list-item-title>Create New</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
        <v-list-group
          no-action
          v-if="
            userPermissions.product_list ||
            userPermissions.product_create ||
            userPermissions.inventory_recon_list ||
            userPermissions.inventory_recon_create ||
            userPermissions.brand_list ||
            userPermissions.brand_create ||
            userPermissions.product_category_list ||
            userPermissions.product_category_create ||
            userPermissions.product_model_list ||
            userPermissions.product_model_create
          "
        >
          <!-- List Group Icon-->
          <v-icon slot="prependIcon">mdi-barcode-scan</v-icon>
          <!-- List Group Title -->
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>Inventory</v-list-item-title>
            </v-list-item-content>
          </template>
          <!-- List Group Items -->
          <v-list-item
            link
            to="/inventory/reconciliation"
            v-if="userPermissions.inventory_recon_list"
          >
            <v-list-item-content>
              <v-list-item-title>Reconciliation</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/product/index"
            v-if="userPermissions.product_list"
          >
            <v-list-item-content>
              <v-list-item-title>Product Lists</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/scan_product"
            v-if="userPermissions.product_create"
          >
            <v-list-item-content>
              <v-list-item-title>Scan Product</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/brand/index"
            v-if="userPermissions.brand_list || userPermissions.brand_create"
          >
            <v-list-item-content>
              <v-list-item-title>Brand</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/product_model/index"
            v-if="
              userPermissions.product_model_list ||
              userPermissions.product_model_create
            "
          >
            <v-list-item-content>
              <v-list-item-title>Product Model</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/product_category/index"
            v-if="
              userPermissions.product_category_list ||
              userPermissions.product_category_create
            "
          >
            <v-list-item-content>
              <v-list-item-title>Product Category</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
        <v-list-group
          no-action
          v-if="
            userPermissions.employee_list ||
            userPermissions.employee_resigned_list ||
            userPermissions.employee_payroll_list ||
            userPermissions.employee_absences_list ||
            userPermissions.employee_overtime_list ||
            userPermissions.employee_holiday_pay_list ||
            userPermissions.employee_loans_list
          "
        >
          <!-- List Group Icon-->
          <v-icon slot="prependIcon">mdi-account-multiple</v-icon>
          <!-- List Group Title -->
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>Employee</v-list-item-title>
            </v-list-item-content>
          </template>
          <!-- List Group Items -->
          <v-list-item
            link
            to="/employee/list"
            v-if="userPermissions.employee_list"
          >
            <v-list-item-content>
              <v-list-item-title>Employee Lists</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/employee/resigned/list"
            v-if="userPermissions.employee_resigned_list"
          >
            <v-list-item-content>
              <v-list-item-title>Resigned</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/employee/payroll/list"
            v-if="userPermissions.employee_payroll_list"
          >
            <v-list-item-content>
              <v-list-item-title>Payroll</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/employee/absences/list"
            v-if="userPermissions.employee_absences_list"
          >
            <v-list-item-content>
              <v-list-item-title>Absences</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/employee/overtime/list"
            v-if="userPermissions.employee_overtime_list"
          >
            <v-list-item-content>
              <v-list-item-title>Overtime</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/employee/holiday_pay/list"
            v-if="userPermissions.employee_holiday_pay_list"
          >
            <v-list-item-content>
              <v-list-item-title>Holiday Pay</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/employee/loans/list"
            v-if="userPermissions.employee_loans_list"
          >
            <v-list-item-content>
              <v-list-item-title>Loans</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
        <v-list-group
          no-action
          v-if="
            userPermissions.file_list ||
            userPermissions.file_create ||
            userPermissions.user_files
          "
        >
          <!-- List Group Icon-->
          <v-icon slot="prependIcon">mdi-folder-multiple-outline</v-icon>
          <!-- List Group Title -->
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>Training</v-list-item-title>
            </v-list-item-content>
          </template>
          <!-- List Group Items -->
          <v-list-item
            link
            to="/training/my_files"
            v-if="userPermissions.user_files && userPermissions.user_files"
          >
            <v-list-item-content>
              <v-list-item-title>My Files</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/training/files_tutorials"
            v-if="userPermissions.file_list"
          >
            <v-list-item-content>
              <v-list-item-title>{{ "Files & Tutorials" }}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
        <v-list-group no-action v-if="userPermissions.expense_particular_list">
          <!-- List Group Icon-->
          <v-icon slot="prependIcon">mdi-file-multiple-outline</v-icon>
          <!-- List Group Title -->
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>Tactical Req.</v-list-item-title>
            </v-list-item-content>
          </template>
          <!-- List Group Items -->
          <v-list-item
            link
            to="/tactical_requisition/list"
            v-if="userPermissions.tactical_requisition_list"
          >
            <v-list-item-content>
              <v-list-item-title>List</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/tactical_requisition/expense_particular"
            v-if="userPermissions.expense_particular_list"
          >
            <v-list-item-content>
              <v-list-item-title class="subtitle-2"
                >Expense Particular</v-list-item-title
              >
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
        <v-list-group
          no-action
          v-if="
            userPermissions.branch_list ||
            userPermissions.branch_create ||
            userPermissions.position_list ||
            userPermissions.position_create ||
            userPermissions.role_list ||
            userPermissions.role_create ||
            userPermissions.permission_list ||
            userPermissions.permission_create
          "
        >
          <!-- List Group Icon-->
          <v-icon slot="prependIcon">mdi-cog</v-icon>
          <!-- List Group Title -->
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>Settings</v-list-item-title>
            </v-list-item-content>
          </template>
          <!-- List Group Items -->
          <v-list-item
            link
            to="/branch/index"
            v-if="userPermissions.branch_list || userPermissions.branch_create"
          >
            <v-list-item-content>
              <v-list-item-title>Branch</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/company/index"
            v-if="
              userPermissions.company_list || userPermissions.company_create
            "
          >
            <v-list-item-content>
              <v-list-item-title>Company</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/position/index"
            v-if="
              userPermissions.position_list || userPermissions.position_create
            "
          >
            <v-list-item-content>
              <v-list-item-title>Position</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/department/index"
            v-if="
              userPermissions.department_list ||
              userPermissions.department_create
            "
          >
            <v-list-item-content>
              <v-list-item-title>Department</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/role/index"
            v-if="userPermissions.role_list || userPermissions.role_create"
          >
            <v-list-item-content>
              <v-list-item-title>Role</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/permission/index"
            v-if="userPermissions.permission_list"
          >
            <v-list-item-content>
              <v-list-item-title>Permission</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
        <!-- <v-list-item
          link
          to="/activity_logs"
          v-if="userPermissions.activity_logs"
        >
          <v-list-item-icon>
            <v-icon>mdi-history</v-icon>
          </v-list-item-icon>
          <v-list-item-title>Activity Logs</v-list-item-title>
        </v-list-item> -->
      </v-list>
    </v-navigation-drawer>
    <v-overlay :absolute="absolute" :value="overlay">
      <v-progress-circular
        :size="70"
        :width="7"
        color="primary"
        indeterminate
      ></v-progress-circular>
    </v-overlay>
    <!-- Content -->
    <router-view />
    <v-footer padless dense dark app>
      <v-col class="text-center" cols="12">
        Copyright © {{ new Date().getFullYear() }} —
        <strong> ADDESSA CORPORATION</strong>
      </v-col>
    </v-footer>
  </v-app>
</template>

<style>
html {
  overflow-y: auto;
} /* show scrollbar when overflow */
</style>

<script>
import axios from "axios";
import { mapState, mapActions } from "vuex";

export default {
  data() {
    return {
      absolute: true,
      overlay: false,
      drawer: true,
      mini: false,
      right: null,
      selectedItem: 1,
      loading: null,
    };
  },

  methods: {
    logout() {
      this.overlay = true;
      axios.get("/api/auth/logout").then(
        (response) => {
          if (response.data.success) {
            this.overlay = false;
            localStorage.removeItem("access_token");
            this.$router.push("/login").catch(() => {});
          }
        },
        (error) => {
          this.overlay = false;
          console.log(error);

          // if unauthenticated (401)
          if (error.response.status == "401") {
            localStorage.removeItem("access_token");
            this.$router.push({ name: "login" });
          }
        }
      );
    },

    userProfile() {
      this.$router.push({ name: "user.profile" }).catch((e) => {});
    },

    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;
        if (
          action == "user-edit" ||
          action == "role-edit" ||
          action == "role-delete" ||
          action == "permission-delete"
        ) {
          this.userRolesPermissions();
        }
      };
    },
    ...mapActions("auth", ["getUser"]),
    ...mapActions("userRolesPermissions", ["userRolesPermissions"]),
  },

  computed: {
    ...mapState("auth", ["user"]),
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");

    this.getUser();
    this.userRolesPermissions();
    // this.websocket();

    // axios.get('http://mis.test/api/user/index', {
    //   headers: {
    //     Authorization: "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMzllNmZlYzc5OTEwZGYwZWE5NGJmODJkMGZmYTEyZGUxMWM5MjgyYzdjNjc5MTE0NGMyZjZlMWMyYTI0MjJhMWY5NTJhNzhjNzJkNTMzYWIiLCJpYXQiOjE2MzU5OTMyOTAsIm5iZiI6MTYzNTk5MzI5MCwiZXhwIjoxNjY3NTI5MjkwLCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.XnybzTFSDCAili4bDAw1mXiuobZRnCSi293LcWH9i__fb2JXiXhUGiYxlhS1iVQt1wg3DxqrWfHZpBWOHBBs3Q-MRUcqvIJ37ZG0Q_Q1jUiGT5lwN-CDiPS6uGFayS3AKSXIU0p56lFwM9FWmahI4avW9420Ad7NkNX87Q3nwMBvF0h3Xjy7vf0Et20BqKw06k-xMrLK2OxJnkVmsgtuPZMHykEv-HxgrXohUGnYFWXN_3K-G6FJV0tuGADLEnTPS0CpPBHpqB0Cec0YXxe1grLCf01axxcJJL9_crL2E_Gtq_vM8KH6l8cF1O6v5Cn7yn0mhejAZeVQu9Y1wIh7RVdkgOtDxfYxoE07MQ9m73swz7HnI6VyZDpDqBOEM-Vcdci0Ubs5IDuJPRu7GzFj7Eq-Vfrx8zJ_SX19u_4xs6vKdJoYJLrIea6wvNup4Qt-Kkv0Vux5FYTdIdPOvmmY6uWLpU0FWY7g4QCmAS5U5xJGWx_U-81usvPXlRsF6LGi_tfKcinluqnprLma9YiHqnbds4dC7RjP3eZmQkrip8mJ3AjtbBp79Iqn0aVbEJ3qE8XJITQH3BVdNgsFyNaYUIPb8fUfwolzhkauj3kGSpfgxTVKS0vcDgA2kZfStaP6bPWls8z2U-MYKahTs5K3i1kTCZ4paNLszY_8SN-Q83c",
    //     // "Access-Control-Allow-Methods": "GET, POST, PATCH, PUT, DELETE, OPTIONS",
    //     //   "Access-Control-Allow-Headers": "Origin, Content-Type, X-Auth-Token"
    //   }
    // }).then((response) => {
    //   console.log(response);
    // }, (error) => {
    //   console.log(error);
    // })
  },
};
</script>