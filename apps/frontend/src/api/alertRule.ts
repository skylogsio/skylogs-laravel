import type { IEndpoint } from "@/@types/endpoint";
import type { ServerResponse } from "@/@types/global";
import axios from "@/lib/axios";

const ALERT_RULE_URL = "alert-rule";
const ALERT_RULE_NOTIFY_URL = "alert-rule-notify";

export function createAlertRule(body: unknown) {
  return axios.post(ALERT_RULE_URL, body);
}

export function updateAlertRule(alertRuleId: unknown, body: unknown) {
  return axios.put(`${ALERT_RULE_URL}/${alertRuleId}`, body);
}

export function deleteAlertRule(alertRuleId: unknown) {
  return axios.delete(`${ALERT_RULE_URL}/${alertRuleId}`);
}

export function getAlertRuleCreateData() {
  return axios.get(`${ALERT_RULE_URL}/create-data`);
}

export function testAlertRule(id: unknown) {
  return axios.post(`${ALERT_RULE_NOTIFY_URL}/test/${id}`);
}

export function silenceAlertRule(id: unknown) {
  return axios.post(`${ALERT_RULE_URL}/silent/${id}`);
}

export async function getAlertFilterEndpointList() {
  return axios
    .get<Array<IEndpoint>>(`${ALERT_RULE_URL}/filter-endpoints`)
    .then((response) => response.data);
}

export async function resolveFiredAlertRule(alertRuleId: unknown) {
  return axios.post<ServerResponse<unknown>>(`${ALERT_RULE_URL}/resolve/${alertRuleId}`);
}
