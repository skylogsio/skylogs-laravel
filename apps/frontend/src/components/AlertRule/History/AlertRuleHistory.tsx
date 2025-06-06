import type { IAlertRule } from "@/@types/alertRule";
import ApiAndNotificationAlertHistory from "@/components/AlertRule/History/ApiAndNotificationAlertHistory";
import PrometheusAlertsHistory from "@/components/AlertRule/History/PrometheusAlertHistory";
import type { AlertRuleType } from "@/utils/alertRuleUtils";

export default function AlertRuleHistory({
  alertId,
  type
}: {
  alertId: IAlertRule["id"];
  type: AlertRuleType;
}) {
  switch (type) {
    case "api":
    case "notification":
      return <ApiAndNotificationAlertHistory alertId={alertId} />;
    case "prometheus":
      return <PrometheusAlertsHistory alertId={alertId} />;
    default:
      return null;
  }
}
