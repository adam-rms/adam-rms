import axios, { Method as MethodType } from "axios";

/* Where is the API hosted?
 * N.B This is a temporary variable to be replaced when auth is added to the app!
 */
export const baseURL = "http://admin.test/";

//TODO: @jbithell will come back and wrap this into a class, and probably remove axios

/**
 * Get data from AdamRMS API
 * @param endpoint API endpoint
 * @param data any parameter data for the endpoint
 * @param method the HTTP Method used
 * @returns response data as an Object
 * @link https://adam-rms.com/docs/api/intro for V1 endpoints
 */
const Api = async (
  endpoint: string,
  data: any = {},
  method: MethodType = "GET",
) => {
  return axios({
    method: method,
    url: baseURL + "api/" + endpoint,
    params: data,
  })
    .then(function (response) {
      if (response.data["result"] == true) {
        return response.data["response"];
      } else {
        return response.data;
      }
    })
    .catch(function (error) {
      console.log(error);
    });
};

export default Api;
