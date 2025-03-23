import axios from "@/lib/axios";

const ALERT_RULE_URL = "alert-rule";

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
