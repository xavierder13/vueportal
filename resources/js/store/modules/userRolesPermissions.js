import axios from 'axios';
import router from '../../router';

const state = {
  userPermissions: {
    user_list: false,
    user_create: false,
    user_edit: false,
    user_delete: false,
    brand_list: false,
    brand_create: false,
    brand_edit: false,
    brand_delete: false,
    branch_list: false,
    product_category_list: false,
    product_category_create: false,
    product_category_edit: false,
    product_category_delete: false,
    branch_list: false,
    branch_create: false,
    branch_edit: false,
    branch_delete: false,
    product_list: false,
    product_list_all: false,
    product_create: false,
    product_edit: false,
    product_delete: false,
    product_clear_list: false,
    product_export: false,
    inventory_recon_list: false,
    inventory_recon_list_all: false,
    inventory_recon_create: false,
    inventory_recon_edit: false,
    inventory_recon_delete: false,
    inventory_recon_audit: false,
    employee_list: false,
    employee_list_all: false,
    employee_list_import: false,
    employee_list_export: false,
    employee_clear_list: false,
    employee_resigned_list: false,
    employee_resigned_list_all: false,
    employee_resigned_import: false,
    employee_resigned_export: false,
    employee_resigned_clear_list: false,
    employee_payroll_list: false,
    employee_payroll_list_all: false,
    employee_payroll_import: false,
    employee_payroll_export: false,
    employee_payroll_clear_list: false,
    employee_absences_list: false,
    employee_absences_list_all: false,
    employee_absences_import: false,
    employee_absences_export: false,
    employee_absences_clear_list: false,
    employee_overtime_list: false,
    employee_overtime_list_all: false,
    employee_overtime_import: false,
    employee_overtime_export: false,
    employee_overtime_clear_list: false,
    employee_holiday_pay_list: false,
    employee_holiday_pay_list_all: false,
    employee_holiday_pay_import: false,
    employee_holiday_pay_export: false,
    employee_holiday_pay_clear_list: false,
    employee_loans_list: false,
    employee_loans_list_all: false,
    employee_loans_import: false,
    employee_loans_export: false,
    employee_loans_clear_list: false,
    role_list: false,
    role_create: false,
    role_edit: false,
    role_delete: false,
    permission_list: false,
    permission_create: false,
    permission_edit: false,
    permission_delete: false,
    activity_logs: false,
  },
  userRoles: {
    administrator: false,
    hr_admin: false,
  },
};

const getters = {};

const actions = {
  async userRolesPermissions({ commit }) {
    let response = await axios.get("/api/user/roles_permissions").then((response) => {

      commit('setUserRoles', response.data.user_roles);
      commit('setUserPermissions', response.data.user_permissions);

    },
      (error) => {
        if (error.response.status == "401") {
          router.push({ name: "unauthorize" });
        }
      });

  },

};

const mutations = {
  setUserRoles(state, roles) {

    let role = state.userRoles;

    role.administrator = roles.includes("Administrator");
    role.hr_admin = roles.includes("HR Admin");
  },
  setUserPermissions(state, permissions) {

    let permission = state.userPermissions;

    // USER RECORD MAINTENANCE PERMISSIONS
    permission.user_list = permissions.includes("user-list");
    permission.user_create = permissions.includes("user-create");
    permission.user_edit = permissions.includes("user-edit");
    permission.user_delete = permissions.includes("user-delete");

    // BRAND RECORD MAINTENANCE PERMISSIONS
    permission.brand_list = permissions.includes("brand-list");
    permission.brand_create = permissions.includes("brand-create");
    permission.brand_edit = permissions.includes("brand-edit");
    permission.brand_delete = permissions.includes("brand-delete");

    // PRODUCT RECORD CATEGORY MAINTENANCE PERMISSIONS
    permission.product_category_list = permissions.includes("product-category-list");
    permission.product_category_create = permissions.includes("product-category-create");
    permission.product_category_edit = permissions.includes("product-category-edit");
    permission.product_category_delete = permissions.includes("product-category-delete");

    // BRAND RECORD MAINTENANCE PERMISSIONS
    permission.branch_list = permissions.includes("branch-list");
    permission.branch_create = permissions.includes("branch-create");
    permission.branch_edit = permissions.includes("branch-edit");
    permission.branch_delete = permissions.includes("branch-delete");

    // PRODUCT RECORD MAINTENANCE PERMISSIONS
    permission.product_list = permissions.includes("product-list");
    permission.product_list_all = permissions.includes("product-list-all");
    permission.product_create = permissions.includes("product-create");;
    permission.product_edit = permissions.includes("product-edit");
    permission.product_delete = permissions.includes("product-delete");
    permission.product_export = permissions.includes("product-export");
    permission.product_clear_list = permissions.includes("product-clear-list");

    // INVENTORY RECONCILIATION RECORD MAINTENANCE PERMISSIONS
    permission.inventory_recon_list = permissions.includes("inventory-recon-list");
    permission.inventory_recon_list_all = permissions.includes("inventory-recon-list-all");
    permission.inventory_recon_create = permissions.includes("inventory-recon-create");;
    permission.inventory_recon_edit = permissions.includes("inventory-recon-edit");
    permission.inventory_recon_delete = permissions.includes("inventory-recon-delete");
    permission.inventory_recon_audit = permissions.includes("inventory-recon-audit");

    // EMPLOYEE RECORD MAINTENANCE PERMISSIONS
    permission.employee_list = permissions.includes("employee-list");
    permission.employee_list_all = permissions.includes("employee-list-all");
    permission.employee_list_import = permissions.includes("employee-list-import");
    permission.employee_list_export = permissions.includes("employee-list-export");
    permission.employee_clear_list = permissions.includes("employee-clear-list");

    // RESIGNED EMPLOYEE RECORD MAINTENANCE PERMISSIONS
    permission.employee_resigned_list = permissions.includes("employee-resigned-list");
    permission.employee_resigned_list_all = permissions.includes("employee-resigned-list-all");
    permission.employee_resigned_import = permissions.includes("employee-resigned-import");
    permission.employee_resigned_export = permissions.includes("employee-resigned-export");
    permission.employee_resigned_clear_list = permissions.includes("employee-resigned-clear-list");

    // PAYROLL RECORD MAINTENANCE PERMISSIONS
    permission.employee_payroll_list = permissions.includes("employee-payroll-list");
    permission.employee_payroll_list_all = permissions.includes("employee-payroll-list-all");
    permission.employee_payroll_import = permissions.includes("employee-payroll-import");
    permission.employee_payroll_export = permissions.includes("employee-payroll-export");
    permission.employee_payroll_clear_list = permissions.includes("employee-payroll-clear-list");

    // EMPLOYEE ABSENCES RECORD MAINTENANCE PERMISSIONS
    permission.employee_absences_list = permissions.includes("employee-absences-list");
    permission.employee_absences_list_all = permissions.includes("employee-absences-list-all");
    permission.employee_absences_import = permissions.includes("employee-absences-import");
    permission.employee_absences_export = permissions.includes("employee-absences-export");
    permission.employee_absences_clear_list = permissions.includes("employee-absences-clear-list");

    // EMPLOYEE OVERTIME RECORD MAINTENANCE PERMISSIONS
    permission.employee_overtime_list = permissions.includes("employee-overtime-list");
    permission.employee_overtime_list_all = permissions.includes("employee-overtime-list-all");
    permission.employee_overtime_import = permissions.includes("employee-overtime-import");
    permission.employee_overtime_export = permissions.includes("employee-overtime-export");
    permission.employee_overtime_clear_list = permissions.includes("employee-overtime-clear-list");

    // EMPLOYEE HOLIDAY PAY RECORD MAINTENANCE PERMISSIONS
    permission.employee_holiday_pay_list = permissions.includes("employee-holiday-pay-list");
    permission.employee_holiday_pay_list_all = permissions.includes("employee-holiday-pay-list-all");
    permission.employee_holiday_pay_import = permissions.includes("employee-holiday-pay-import");
    permission.employee_holiday_pay_export = permissions.includes("employee-holiday-pay-export");
    permission.employee_holiday_pay_clear_list = permissions.includes("employee-holiday-pay-clear-list");

    // EMPLOYEE LOANS RECORD MAINTENANCE PERMISSIONS
    permission.employee_loans_list = permissions.includes("employee-loans-list");
    permission.employee_loans_list_all = permissions.includes("employee-loans-list-all");
    permission.employee_loans_import = permissions.includes("employee-loans-import");
    permission.employee_loans_export = permissions.includes("employee-loans-export");
    permission.employee_loans_clear_list = permissions.includes("employee-loans-clear-list");

    // PERMISSION RECORD MAINTENANCE PERMISSIONS
    permission.permission_list = permissions.includes("permission-list");
    permission.permission_create = permissions.includes("permission-create");
    permission.permission_edit = permissions.includes("permission-edit");
    permission.permission_delete = permissions.includes("permission-delete");

    // ROLE RECORD MAINTENANCE PERMISSIONS
    permission.role_list = permissions.includes("role-list");
    permission.role_create = permissions.includes("role-create");
    permission.role_edit = permissions.includes("role-edit");
    permission.role_delete = permissions.includes("role-delete");

    // ACTIVITY LOG PERMISSIONS
    permission.activity_logs = permissions.includes("activity-logs");
  },

};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}