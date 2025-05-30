"use client";
import { useRef, useState } from "react";

import type { IDataSource } from "@/@types/dataSource";
import type { CreateUpdateModal } from "@/@types/global";
import DataSourceModal from "@/app/[locale]/data-source/DataSourceModal";
import DeleteDataSourceModal from "@/app/[locale]/data-source/DeleteDataSourceModal";
import ActionColumn from "@/components/ActionColumn";
import ConnectionStatus from "@/components/ConnectionStatus";
import DataSourceType from "@/components/DataSource/DataSourceType";
import Table from "@/components/Table/SmartTable";
import { type TableComponentRef } from "@/components/Table/types";

export default function DataSource() {
  const tableRef = useRef<TableComponentRef>(null);
  const [modalData, setModalData] = useState<CreateUpdateModal<IDataSource>>(null);
  const [deleteModalData, setDeleteModalData] = useState<IDataSource | null>(null);

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
      <Table<IDataSource>
        ref={tableRef}
        title="Data Sources"
        url="data-source"
        defaultPage={1}
        defaultPageSize={10}
        refetchInterval={5000}
        columns={[
          { header: "Row", accessorFn: (_, index) => ++index },
          { header: "Name", accessorKey: "name" },
          {
            header: "Type",
            cell: ({ row }) => <DataSourceType type={row.original.type} />
          },
          {
            header: "Status",
            cell: ({ row }) => <ConnectionStatus status={row.original.status} />
          },
          {
            header: "Action",
            cell: ({ row }) => (
              <ActionColumn
                onEdit={() => setModalData(row.original)}
                onDelete={() => setDeleteModalData(row.original)}
              />
            )
          }
        ]}
        onCreate={() => setModalData("NEW")}
      />
      {modalData && (
        <DataSourceModal
          open={!!modalData}
          onClose={() => setModalData(null)}
          data={modalData}
          onSubmit={handleRefreshData}
        />
      )}
      {deleteModalData && (
        <DeleteDataSourceModal
          open={!!deleteModalData}
          onClose={() => setDeleteModalData(null)}
          data={deleteModalData}
          onAfterDelete={handleDelete}
        />
      )}
    </>
  );
}
