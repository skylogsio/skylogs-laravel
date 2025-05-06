import type { AxiosResponse } from "axios";

import type { ModalContainerProps } from "@/components/Modal/types";

export type CreateUpdateModal<T> = T | "NEW" | null;

export interface BasicCreateOrUpdateModalProps
  extends Pick<ModalContainerProps, "open" | "onClose"> {
  onSubmit: () => void;
}

export type ConnectionStatusType = "connected" | "disconnected" | "warning";

export type ServerSelectableDataType = Array<{ id: string; name: string }>;

//TODO: Should add the type of message which comes from the server
type ErrorResponse = { status: false; message: unknown };
type SuccessResponse<T> = { status: true; data: T };
export type ServerResponse<T> = AxiosResponse<ErrorResponse | SuccessResponse<T>>;
