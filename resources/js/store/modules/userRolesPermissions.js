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
    branch_create: false,
    branch_edit: false,
    branch_delete: false,
    product_list: false,
    product_create: false,
    product_edit: false,
    product_delete: false,
    product_clear_list: false,
    product_export: false,
    employee_list: false,
    employee_list_import: false,
    employee_clear_list: false,
    employee_resigned_list: false,
    employee_resigned_import: false,
    employee_resigned_clear_list: false,
    employee_payroll_list: false,
    employee_payroll_import: false,
    employee_payroll_clear_list: false,
    employee_absences_list: false,
    employee_absences_import: false,
    employee_absences_clear_list: false,
    employee_overtime_list: false,
    employee_overtime_import: false,
    employee_overtime_clear_list: false,
    employee_holiday_pay_list: false,
    employee_holiday_pay_import: false,
    employee_holiday_pay_clear_list: false,
    employee_loans_list: false,
    employee_loans_import: false,
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
    state.userRoles.administrator = roles.includes("Administrator");
  },
  setUserPermissions(state, permissions) {
    state.userPermissions.user_list = permissions.includes("user-list");
    state.userPermissions.user_create = permissions.includes("user-create");;
    state.userPermissions.user_edit = permissions.includes("user-edit");
    state.userPermissions.user_delete = permissions.includes("user-delete");
    state.userPermissions.brand_list = permissions.includes("brand-list");
    state.userPermissions.brand_create = permissions.includes("brand-create");;
    state.userPermissions.brand_edit = permissions.includes("brand-edit");
    state.userPermissions.brand_delete = permissions.includes("brand-delete");
    state.userPermissions.branch_list = permissions.includes("branch-list");
    state.userPermissions.branch_create = permissions.includes("branch-create");;
    state.userPermissions.branch_edit = permissions.includes("branch-edit");
    state.userPermissions.branch_delete = permissions.includes("branch-delete");
    state.userPermissions.product_list = permissions.includes("product-list");
    state.userPermissions.product_create = permissions.includes("product-create");;
    state.userPermissions.product_edit = permissions.includes("product-edit");
    state.userPermissions.product_delete = permissions.includes("product-delete");
    state.userPermissions.product_export = permissions.includes("product-export");
    state.userPermissions.product_clear_list = permissions.includes("product-clear-list");
    state.userPermissions.employee_list = permissions.includes("employee-list");
    state.userPermissions.employee_list_import = permissions.includes("employee-list-import");
    state.userPermissions.employee_clear_list = permissions.includes("employee-clear-list");
    state.userPermissions.employee_resigned_list = permissions.includes("employee-resigned-list");
    state.userPermissions.employee_resigned_import = permissions.includes("employee-resigned-import");
    state.userPermissions.employee_resigned_clear_list = permissions.includes("employee-resigned-clear-list");
    state.userPermissions.employee_payroll_list = permissions.includes("employee-payroll-list");
    state.userPermissions.employee_payroll_import = permissions.includes("employee-payroll-import");
    state.userPermissions.employee_payroll_clear_list = permissions.includes("employee-payroll-clear-list");
    state.userPermissions.employee_absences_list = permissions.includes("employee-absences-list");
    state.userPermissions.employee_absences_import = permissions.includes("employee-absences-import");
    state.userPermissions.employee_absences_clear_list = permissions.includes("employee-absences-clear-list");
    state.userPermissions.employee_overtime_list = permissions.includes("employee-overtime-list");
    state.userPermissions.employee_overtime_import = permissions.includes("employee-overtime-import");
    state.userPermissions.employee_overtime_clear_list = permissions.includes("employee-overtime-clear-list");
    state.userPermissions.employee_holiday_pay_list = permissions.includes("employee-holiday-pay-list");
    state.userPermissions.employee_holiday_pay_import = permissions.includes("employee-holiday-pay-import");
    state.userPermissions.employee_holiday_pay_clear_list = permissions.includes("employee-holiday-pay-clear-list");
    state.userPermissions.employee_loans_list = permissions.includes("employee-loans-list");
    state.userPermissions.employee_loans_import = permissions.includes("employee-loans-import");
    state.userPermissions.employee_loans_clear_list = permissions.includes("employee-loans-clear-list");
    state.userPermissions.permission_list = permissions.includes("permission-list");
    state.userPermissions.permission_create = permissions.includes("permission-create");
    state.userPermissions.permission_edit = permissions.includes("permission-edit");
    state.userPermissions.permission_delete = permissions.includes("permission-delete");
    state.userPermissions.role_list = permissions.includes("role-list");
    state.userPermissions.role_create = permissions.includes("role-create");
    state.userPermissions.role_edit = permissions.includes("role-edit");
    state.userPermissions.role_delete = permissions.includes("role-delete");
    state.userPermissions.activity_logs = permissions.includes("activity-logs");
  },

};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}