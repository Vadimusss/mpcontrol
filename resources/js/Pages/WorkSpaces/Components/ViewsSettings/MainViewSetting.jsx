import InputError from '@/Components/InputError';

export default function MainViewSetting({ data, errors, processing, handleSettingChange }) {
    return (
        <div>
            <label htmlFor="days" className="block text-sm font-medium text-gray-700 mb-2">
                Период
            </label>
            <select
                value={data.settings?.days || ''}
                id="days"
                className="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200"
                onChange={(e) => handleSettingChange('days', Number(e.target.value))}
                disabled={processing}
            >
                <option value="" disabled>Выберите количество дней</option>
                <option value="30">30 дней</option>
                <option value="14">14 дней</option>
                <option value="7">7 дней</option>
            </select>
            <InputError message={errors.settings} className="mt-2" />
        </div>
    )
}