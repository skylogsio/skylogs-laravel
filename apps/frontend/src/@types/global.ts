import type { ModalContainerProps } from "@/components/Modal/types";

export type CreateUpdateModal<T> = T | "NEW" | null;

export interface BasicCreateOrUpdateModalProps
  extends Pick<ModalContainerProps, "open" | "onClose"> {
  onSubmit: () => void;
}

export type ConnectionStatusType = "connected" | "disconnected" | "warning";
