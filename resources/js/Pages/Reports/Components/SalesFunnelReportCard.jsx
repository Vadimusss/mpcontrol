import React, { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';

export default function SalesFunnelReportCard({ shopId, report, goodLists, handleDownload }) {
  const [selectedGoodListId, setSelectedGoodListId] = useState(
    goodLists.length === 0 ? null : goodLists[0].id
  );

  const currentDate = new Date().toISOString().split('T')[0];
  const defaultBeginDate = new Date();
  defaultBeginDate.setDate(defaultBeginDate.getDate() - 30);
  const defaultBeginDateFormatted = defaultBeginDate.toISOString().split('T')[0];

  const [selectedBeginDate, setSelectedBeginDate] = useState(defaultBeginDateFormatted);
  const [selectedEndDate, setSelectedEndDate] = useState(currentDate);

  return (
    <div className="bg-white border border-gray-300 rounded-md shadow-sm p-4 mb-2">
      <p className="mb-4 font-semibold text-gray-900">
        Выгрузка: <span className="font-normal">{report.type.name}</span>
      </p>
      <div className="flex gap-8 mb-4">
        <div className="w-64">
          <p className="block mb-2 text-sm font-medium text-gray-900">
            {selectedGoodListId === null ? 'Создайте список товаров' : 'Выберите список товаров'}
          </p>
          <select
            onChange={(e) => setSelectedGoodListId(e.target.value)}
            className="w-full p-2 mb-4 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          >
            {goodLists && goodLists.map(({ id, name }) => (
              <option value={id} key={id}>{name}</option>
            ))}
          </select>
          <PrimaryButton
            className="max-w-fit"
            onClick={() => handleDownload(shopId, report, selectedBeginDate, selectedEndDate, selectedGoodListId)}
            disabled={!selectedGoodListId}
          >
            Скачать
          </PrimaryButton>
        </div>
        <div className="w-40">
          <label className="block mb-2 text-sm font-medium text-gray-900">Начальная дата:</label>
          <input
            type="date"
            value={selectedBeginDate}
            onChange={(e) => setSelectedBeginDate(e.target.value)}
            className="w-full p-2 mb-4 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          />
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