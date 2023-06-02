
import axios from 'axios';
import router from '../../router';

const state = {
  permissions: [],
  roles: [],
  userRolesPermissionsIsLoaded: false,
};

const getters = {
  hasRole: (state) => (role) => {
      return state.roles.includes(role);
  },
  hasAnyRole: (state) => (role) => {
    let hasRole = false;
    role.forEach(value => {
      if(state.roles.includes(value))
      {
        hasRole = true;
      }
    });
    return hasRole;
  },
  hasPermission: (state) => (permission) => {
      return state.permissions.includes(permission);
  },
  hasAnyPermission: (state) => (permission) => {

  }
};

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
    state.roles = roles;
  },
  setUserPermissions(state, permissions) {
    state.permissions = permissions;
    // set true if user roles and permissions value successfully assigned
    state.userRolesPermissionsIsLoaded = true;
  },

};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}
