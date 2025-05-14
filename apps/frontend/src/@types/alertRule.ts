import type { IEndpoint } from "@/@types/endpoint";
import type { IUser } from "@/@types/user";
import { type AlertRuleType } from "@/utils/alertRuleUtils";

export interface IAlertRuleCreateData {
  endpoints: IEndpoint[];
  users: IUser[];
}

export type AlertRuleStatus = "resolved" | "warning" | "critical" | "triggered" | "unknown";

export interface IAlertRule {
  name: string;
  type: AlertRuleType;
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
  is_silent: boolean;
  count_endpoints: number;
  tags: string[];
}

export interface IAlertRuleEndpoints {
  alertEndpoints: Array<IEndpoint>;
  selectableEndpoints: Array<IEndpoint>;
}

export interface IAlertRuleUsers {
  alertUsers: Array<IUser>;
  selectableUsers: Array<IUser>;
}
