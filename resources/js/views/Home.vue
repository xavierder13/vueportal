<template>
  <v-app>
    <!-- Navbar -->
    <v-app-bar dense dark app>
      <v-btn icon @click.stop="drawer = !drawer">
        <v-app-bar-nav-icon></v-app-bar-nav-icon>
      </v-btn>

      <v-spacer></v-spacer>

      <v-btn @click="logout">
        <v-icon>mdi-logout </v-icon>
        Logout
      </v-btn>
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
            userPermissions.inventory_recon_create
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
            userPermissions.brand_list ||
            userPermissions.brand_create ||
            userPermissions.product_category_list ||
            userPermissions.product_category_create ||
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
            to="/brand/index"
            v-if="userPermissions.brand_list || userPermissions.brand_create"
          >
            <v-list-item-content>
              <v-list-item-title>Brand</v-list-item-title>
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
            to="/position/index"
            v-if="userPermissions.position_list || userPermissions.position_create"
          >
            <v-list-item-content>
              <v-list-item-title>Position</v-list-item-title>
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
  </v-app>
</template>

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
  },
};
</script>