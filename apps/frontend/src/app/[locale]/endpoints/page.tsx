"use client";
import { useRef, useState } from "react";

import type { IEndpoint } from "@/@types/endpoint";
import { CreateUpdateModal } from "@/@types/global";
import DeleteEndPointModal from "@/app/[locale]/endpoints/DeleteEndPointModal";
import ActionColumn from "@/components/ActionColumn";
import Table from "@/components/Table";
import { type TableComponentRef } from "@/components/Table/types";
import { renderEndPointChip } from "@/utils/endpointVariants";

import EndPointModal from "./EndPointModal";

export default function EndPoints() {
  const tableRef = useRef<TableComponentRef>(null);
  const [modalData, setModalData] = useState<CreateUpdateModal<IEndpoint>>(null);
  const [deleteModalData, setDeleteModalData] = useState<IEndpoint | null>(null);

  function handleEdit(data: IEndpoint) {
    setModalData(data);
  }

  function handleRefreshData() {
    if (tableRef.current) {
      tableRef.current.refreshData();
    }
  }

  function handleDelete() {
    setDeleteModalData(null);
    handleRefreshData();
  }

  return (
    <>
      <Table<IEndpoint>
        ref={tableRef}
        title="EndPoints"
        url="endpoint"
        defaultPage={1}
        defaultPageSize={10}
        columns={[
          { header: "Row", accessorFn: (_, index) => index },
          { header: "Name", accessorKey: "name" },
          {
            header: "Type",
            accessorKey: "type",
            cell: ({ cell }) => renderEndPointChip(cell.getValue())
          },
          {
            header: "Value",
            accessorFn: (row) => (row.type === "telegram" ? row.chatId : row.value)
          },
          {
            header: "Action",
            cell: ({ row }) => (
              <ActionColumn
                onEdit={() => handleEdit(row.original)}
                onDelete={() => setDeleteModalData(row.original)}
              />
            )
          }
        ]}
        onCreate={() => setModalData("NEW")}
      />
      {modalData && (
        <EndPointModal
          open={!!modalData}
          onClose={() => setModalData(null)}
          data={modalData}
          onSubmit={handleRefreshData}
        />
      )}
      {deleteModalData && (
        <DeleteEndPointModal
          open={!!deleteModalData}
          onClose={() => setDeleteModalData(null)}
          data={deleteModalData}
          onDelete={handleDelete}
        />
      )}
    </>
  );
}
