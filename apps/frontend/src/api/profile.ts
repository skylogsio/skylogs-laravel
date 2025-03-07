import axios from "@/utils/axios";

export function getMyInfo() {
  return axios.post("auth/me");
}
