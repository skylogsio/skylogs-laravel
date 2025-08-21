export type StatusStatus = "resolve" | "warning" | "critical";

export interface IStatusCard {
  id: string;
  name: string;
  status: StatusStatus;
  criticalCount: number;
  warningCount: number;
  created_at: string;
  updated_at: string;
}
