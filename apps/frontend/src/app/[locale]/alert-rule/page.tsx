"use client";
import { useRef, useState } from "react";

import type { IAlertRule } from "@/@types/alertRule";
import type { CreateUpdateModal } from "@/@types/global";
import AlertRuleActionColumn from "@/app/[locale]/alert-rule/AlertRuleActionColumn";
import TagsCell from "@/app/[locale]/alert-rule/TagsCell";
import AlertRuleStatus from "@/components/AlertRule/AlertRuleStatus";
import AlertRuleType from "@/components/AlertRule/AlertRuleType";
import Table from "@/components/Table/SmartTable";
import type { TableComponentRef } from "@/components/Table/types";

import AlertRuleFilter from "./AlertRuleFilter";
import AlertRuleModal from "./AlertRuleModal";
import DeleteAlertRuleModal from "./DeleteAlertRuleModal";
import NotifyModal from "./NotifyModal";

export default function AlertRule() {
  const tableRef = useRef<TableComponentRef>(null);
  const [modalData, setModalData] = useState<CreateUpdateModal<IAlertRule>>(null);
  const [deleteModalData, setDeleteModalData] = useState<IAlertRule | null>(null);

  function handleRefreshData() {
    if (tableRef.current) {
      tableRef.current.refreshData();
    }
  }

  function handleAfterDelete() {
    handleRefreshData();
    setDeleteModalData(null);
  }

  return (
    <>
      <Table<IAlertRule>
        ref={tableRef}
        title="Alert Rule"
        url="alert-rule"
        filterComponent={({ onChange }) => <AlertRuleFilter onChange={onChange} />}
        defaultPageSize={10}
        columns={[
          { header: "Row", accessorFn: (_, index) => ++index },
          { header: "Name", accessorKey: "name" },
          {
            header: "Type",
            cell: ({ row }) => <AlertRuleType type={row.original.type} />
          },
          {
            header: "Notify",
            cell: ({ row }) => (
              <NotifyModal
                alertId={row.original.id}
                numberOfEndpoints={row.original.count_endpoints}
                onClose={handleRefreshData}
              />
            )
          },
          {
            header: "Status",
            cell: ({ row }) => (
              <AlertRuleStatus id={row.original.id} status={row.original.status_label} />
            )
          },
          {
            header: "Tags",
            cell: ({ row }) => <TagsCell tags={row.original.tags} />
          },
          {
            header: "Action",
            cell: ({ row }) => (
              <AlertRuleActionColumn
                rowId={row.original.id}
                onEdit={() => setModalData(row.original)}
                onDelete={() => setDeleteModalData(row.original)}
              />
            )
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
      {deleteModalData && (
        <DeleteAlertRuleModal
          open={!!deleteModalData}
          onClose={() => setDeleteModalData(null)}
          onAfterDelete={handleAfterDelete}
          data={deleteModalData}
        />
      )}
    </>
  );
}
