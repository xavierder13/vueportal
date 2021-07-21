import axios from 'axios';
import router from '../../router';

const state = {
  user: "",
};

const getters = {};

const actions = {
  getUser({ commit }) {
    axios.get("/api/auth/init").then(
      (response) => {
        commit('setUser', response.data.user);
        
      },
      (error) => {
        // if unauthenticated (401)
        if (error.response.status == "401") {
          localStorage.removeItem("access_token");
          router.push('/login');
        }
      }
    );
  },
};

const mutations = {
  setUser(state, data) {
    state.user = data;
  }
};

export default {  
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}