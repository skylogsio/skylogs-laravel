"use client";
import { useRef, useState } from "react";

import type { IDataSource } from "@/@types/dataSource";
import type { CreateUpdateModal } from "@/@types/global";
import ActionColumn from "@/components/ActionColumn";
import ConnectionStatus from "@/components/ConnectionStatus";
import DataSourceChip from "@/components/DataSourceChip";
import Table from "@/components/Table";
import type { TableComponentRef } from "@/components/Table/types";

import AlertRuleModal from "./AlertRuleModal";

export default function AlertRule() {
  const tableRef = useRef<TableComponentRef>(null);
  const [modalData, setModalData] = useState<CreateUpdateModal<IDataSource>>(null);

  function handleRefreshData() {
    if (tableRef.current) {
      tableRef.current.refreshData();
    }
  }

  return (
    <>
      <Table<IDataSource>
        ref={tableRef}
        title="Alert Rule"
        url="data-source"
        defaultPage={1}
        defaultPageSize={10}
        columns={[
          { header: "Row", accessorFn: (_, index) => ++index },
          { header: "Name", accessorKey: "name" },
          {
            header: "Type",
            cell: ({ row }) => <DataSourceChip type={row.original.type} />
          },
          {
            header: "Status",
            cell: ({ row }) => <ConnectionStatus status={row.original.status} />
          },
          {
            header: "Action",
            cell: () => <ActionColumn />
          }
        ]}
        onCreate={() => setModalData("NEW")}
      />
      {modalData && (
        <AlertRuleModal
          open={!!modalData}
          onClose={() => setModalData(null)}
          data={modalData}
          onSubmit={handleRefreshData}
        />
      )}
    </>
  );
}
