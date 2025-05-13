import InputError from '@/Components/InputError';

export default function SizesViewSetting({ data, errors, processing, handleSettingChange }) {
    return (
        <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
                Выберите столбцы
            </label>
            <div className="space-y-2">
                {['currency', 'discount', 'clubDiscount', 'editableSizePrice'].map(column => (
                    <div key={column} className="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            checked={data.settings.columns?.includes(column) || false}
                            onChange={(e) => {
                                const columns = data.settings.columns || [];
                                if (e.target.checked) {
                                    handleSettingChange('columns', [...columns, column]);
                                } else {
                                    handleSettingChange('columns', columns.filter(col => col !== column));
                                }
                            }}
                            className="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                            disabled={processing}
                        />
                        <span className="text-sm text-gray-700">{column}</span>
                    </div>
                ))}
            </div>
            <InputError message={errors.settings} className="mt-2" />
        </div>
    )
}