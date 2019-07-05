import axios from 'axios'

export default {
  get (resource, slug) {
    return new Promise((resolve, reject) => {
      axios
        .get(`${resource}/${slug}`)
        .then(response => {
          if (response.data.success === true) {
            resolve(response)
          }
        })
        .catch(() => reject)
    })
  },
  post (resource, data) {
    return new Promise((resolve, reject) => {
      axios
        .post(resource, data)
        .then(response => {
          if (response.data.success === true) {
            resolve(response)
          }
        })
        .catch(() => reject)
    })
  },
  put (resource, data) {
    return new Promise((resolve, reject) => {
      axios
        .put(resource, data)
        .then(response => {
          if (response.data.success === true) {
            resolve(response)
          }
        })
        .catch(() => reject)
    })
  },
  getSearch (resource, slug) {
    return this.get(resource, slug)
  },
  getSearches (resource, params) {
    return new Promise((resolve, reject) => {
      axios
        .get(`${resource}`, { 'params': params })
        .then(response => {
          if (response.data.success === true) {
            resolve(response)
          }
        })
        .catch(() => reject)
    })
  },
  addSearch (resource, data) {
    return this.post(resource, data)
  },
  editSearch (resource, data) {
    return this.put(resource, data)
  },
  deleteSearch (resource) {
    return new Promise((resolve, reject) => {
      axios
        .delete(resource)
        .then(response => {
          if (response.data.success === true) {
            resolve(response)
          }
        })
        .catch(() => reject)
    })
  },
  exportSearch (resource, data) {
    return this.post(resource, data)
  }
}
