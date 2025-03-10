import React, { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';

export default function StocksAndOrdersReportCard({ shopId, report, handleDownload }) {
  const currentDate = new Date().toISOString().split('T')[0];
  const defaultBeginDate = new Date();
  defaultBeginDate.setDate(defaultBeginDate.getDate() - 1);
  const defaultBeginDateFormatted = defaultBeginDate.toISOString().split('T')[0];
  const [selectedBeginDate, setSelectedBeginDate] = useState(defaultBeginDateFormatted);
  const [selectedEndDate, setSelectedEndDate] = useState(currentDate);

  return (
    <div className="bg-white border border-gray-300 rounded-md shadow-sm p-4 mb-2">
      <p className="mb-4 font-semibold text-gray-900">
        Выгрузка: <span className="font-normal">{report.type.name}</span>
      </p>
      <div className="flex items-end gap-8 mb-4">
        <PrimaryButton
          className="max-w-fit mr-auto"
          onClick={() => handleDownload(shopId, report, selectedBeginDate, selectedEndDate)}
        >
          Скачать
        </PrimaryButton>
        <div className="w-40">
          <label className="block mb-2 text-sm font-medium text-gray-900">Начальная дата:</label>
          <input
            type="date"
            value={selectedBeginDate}
            max={selectedEndDate}
            onChange={(e) => setSelectedBeginDate(e.target.value)}
            className="w-full p-2 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <div className="w-40">
          <label className="block mb-2 text-sm font-medium text-gray-900">Конечная дата:</label>
          <input
            type="date"
            value={selectedEndDate}
            min={selectedBeginDate}
            max={currentDate}
            onChange={(e) => setSelectedEndDate(e.target.value)}
            className="w-full p-2 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
      </div>
    </div>
  );
}