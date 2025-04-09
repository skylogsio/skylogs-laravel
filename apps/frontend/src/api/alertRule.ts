"use server";

import type { IAlertRuleCreateData } from "@/@types/alertRule";
import type { IEndpoint } from "@/@types/endpoint";
import type { ServerResponse } from "@/@types/global";
import axios from "@/lib/axios";

const ALERT_RULE_URL = "alert-rule";
const ALERT_RULE_NOTIFY_URL = "alert-rule-notify";

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
