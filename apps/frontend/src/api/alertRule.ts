"use server";

import type {
  IAlertRuleCreateData,
  IAlertRuleEndpoints,
  IAlertRuleUsers
} from "@/@types/alertRule";
import type { IEndpoint } from "@/@types/endpoint";
import type { ServerResponse } from "@/@types/global";
import axios from "@/lib/axios";

const ALERT_RULE_URL = "alert-rule";
const ALERT_RULE_NOTIFY_URL = "alert-rule-notify";
const ALERT_RULE_USER_URL = "alert-rule-user";
const ALERT_RULE_TAGS_URL = "alert-rule-tag";

const ALERT_RULE_PROMETHEUS_URL = "prometheus";

export async function createAlertRule(body: unknown) {
  return axios
    .post<ServerResponse<unknown>>(ALERT_RULE_URL, body)
    .then((response) => response.data);
}

export async function updateAlertRule(alertRuleId: unknown, body: unknown) {
  return axios
    .put<ServerResponse<unknown>>(`${ALERT_RULE_URL}/${alertRuleId}`, body)
    .then((response) => response.data);
}

export async function deleteAlertRule(alertRuleId: unknown) {
  return axios
    .delete<ServerResponse<unknown>>(`${ALERT_RULE_URL}/${alertRuleId}`)
    .then((response) => response.data);
}

export async function getAlertRuleCreateData() {
  return axios
    .get<IAlertRuleCreateData>(`${ALERT_RULE_URL}/create-data`)
    .then((response) => response.data);
}

export async function testAlertRule(id: unknown) {
  return axios
    .post<ServerResponse<unknown>>(`${ALERT_RULE_NOTIFY_URL}/test/${id}`)
    .then((response) => response.data);
}

export async function silenceAlertRule(id: unknown) {
  return axios
    .post<ServerResponse<unknown>>(`${ALERT_RULE_URL}/silent/${id}`)
    .then((response) => response.data);
}

export async function getAlertFilterEndpointList() {
  return axios
    .get<Array<IEndpoint>>(`${ALERT_RULE_URL}/filter-endpoints`)
    .then((response) => response.data);
}

export async function resolveFiredAlertRule(alertRuleId: unknown) {
  return axios
    .post<ServerResponse<unknown>>(`${ALERT_RULE_URL}/resolve/${alertRuleId}`)
    .then((response) => response.data);
}

export async function getAlertRuleEndpointsList(alertRuleId: unknown) {
  return axios
    .get<IAlertRuleEndpoints>(`${ALERT_RULE_NOTIFY_URL}/${alertRuleId}`)
    .then((response) => response.data);
}

export async function addEndpointToAlertRule(alertRuleId: unknown, endpointIds: string[]) {
  return axios
    .put<
      ServerResponse<unknown>
    >(`${ALERT_RULE_NOTIFY_URL}/${alertRuleId}`, { endpoint_ids: endpointIds })
    .then((response) => response.data);
}

export async function removeEndpointFromAlertRule(alertRuleId: unknown, endpointId: unknown) {
  return axios
    .delete<ServerResponse<unknown>>(`${ALERT_RULE_NOTIFY_URL}/${alertRuleId}/${endpointId}`)
    .then((response) => response.data);
}

export async function getAlertRuleUsersList(alertRuleId: unknown) {
  return axios
    .get<IAlertRuleUsers>(`${ALERT_RULE_USER_URL}/${alertRuleId}`)
    .then((response) => response.data);
}

export async function addUsersToAlertRule(alertRuleId: unknown, userIds: string[]) {
  return axios
    .put<ServerResponse<unknown>>(`${ALERT_RULE_USER_URL}/${alertRuleId}`, { user_ids: userIds })
    .then((response) => response.data);
}

export async function removeUserFromAlertRule(alertRuleId: unknown, userId: unknown) {
  return axios
    .delete<ServerResponse<unknown>>(`${ALERT_RULE_USER_URL}/${alertRuleId}/${userId}`)
    .then((response) => response.data);
}

export async function getAlertRuleTags() {
  return axios.get<string[]>(`${ALERT_RULE_TAGS_URL}`).then((response) => response.data);
}

export async function getPrometheusAlertRuleName() {
  return axios
    .get<string[]>(`${ALERT_RULE_PROMETHEUS_URL}/rules`)
    .then((response) => response.data);
}

export async function getPrometheusLabels() {
  return axios
    .get<string[]>(`${ALERT_RULE_PROMETHEUS_URL}/labels`)
    .then((response) => response.data);
}

export async function getPrometheusLabelValues(label: string) {
  return axios
    .get<string[]>(`${ALERT_RULE_PROMETHEUS_URL}/label-values/${label}`)
    .then((response) => response.data);
}
