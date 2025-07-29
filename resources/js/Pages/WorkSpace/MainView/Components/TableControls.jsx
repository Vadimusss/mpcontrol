import React from 'react';
import { MinusIcon, PlusIcon } from '@heroicons/react/24/outline';
import { tableClasses } from '../styles';

export const TableControls = React.memo(({
  showOnlySelected,
  allExpanded,
  onToggleShowOnlySelected,
  onToggleAllRows
}) => {
  return (
    <div className="flex items-center gap-1">
      <input
        type="checkbox"
        checked={showOnlySelected}
        onChange={onToggleShowOnlySelected}
        className={tableClasses.checkbox}
      />
      <button
        onClick={onToggleAllRows}
        className={tableClasses.expandButton}
      >
        {allExpanded ? (
          <MinusIcon className={tableClasses.icon} />
        ) : (
          <PlusIcon className={tableClasses.icon} />
        )}
      </button>
    </div>
  );
});
