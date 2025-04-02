import type { IEndpoint } from "@/@types/endpoint";
import type { IUser } from "@/@types/user";

export interface IAlertRuleCreateData {
  prometheusDataSources: string[];
  grafanaDataSources: string[];
  splunkDataSources: string[];
  endpoints: IEndpoint[];
  users: IUser[];
}

export type AlertRuleStatus = "resolved" | "warning" | "fire";

export interface IAlertRule {
  name: string;
  type: "api" | "manual" | string;
  user_id: string;
  enableAutoResolve: boolean;
  autoResolveMinutes: number;
  updated_at: Date;
  created_at: Date;
  endpoint_ids: string[];
  user_ids: string[];
  id: string;
  hasAdminAccess: boolean;
  status_label: AlertRuleStatus;
  notify?: number;
  is_silent: boolean;
}
