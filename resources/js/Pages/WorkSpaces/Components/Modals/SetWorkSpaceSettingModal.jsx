import SecondaryButton from '@/Components/SecondaryButton';
import PrimaryButton from '@/Components/PrimaryButton';
import InputError from '@/Components/InputError';
import MainViewSetting from '@/Pages/WorkSpaces/Components/ViewsSettings/MainViewSetting';
import SizeViewSetting from '@/Pages/WorkSpaces/Components/ViewsSettings/SizesViewSetting';
import Modal from '@/Components/Modal';
import { useForm } from '@inertiajs/react';

export default function SetWorkSpaceSettingModal({ shopId, workSpace, goodLists, views, maxWidth, isOpen, closeModal }) {
  const { data, setData, patch, processing, errors } = useForm({
    goodListId: goodLists[0].id,
    view_id: workSpace.view_settings.view_id,
    settings: JSON.parse(workSpace.view_settings.settings),
  });

  const selectedView = views.find(view => view.id === data.view_id);

  const handleViewChange = (e) => {
    setData({
      ...data,
      'view_id': Number(e.target.value),
      'settings': {},
    });
  };

  const handleSettingChange = (key, value) => {
    setData('settings', { ...data.settings, [key]: value });
  };

  const submitSetWorkSpaceSetting = (e) => {
    e.preventDefault();
    patch(route('shops.workspaces.update', { shop: shopId, workspace: workSpace.id }), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    });
  };

  return (
    <Modal maxWidth={maxWidth} show={isOpen} onClose={closeModal}>
      <div className="p-8">
        <h2 className="text-lg font-bold text-gray-800 mb-6">
          Настройки рабочей области {workSpace.name}
        </h2>
        <form onSubmit={submitSetWorkSpaceSetting} className="space-y-6">
          <div>
            <label htmlFor="goodLists" className="block text-sm font-medium text-gray-700 mb-2">
              Выберите список товаров
            </label>
            <select
              onChange={(e) => setData('goodListId', e.target.value)}
              id="goodLists"
              className="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200"
            >
              {goodLists && goodLists.map(({ id, name }) => (
                <option value={id} key={id}>{name}</option>
              ))}
            </select>
            <InputError message={errors.goodListId} className="mt-2" />
          </div>

          <div>
            <label htmlFor="views" className="block text-sm font-medium text-gray-700 mb-2">
              Выберите представление
            </label>
            <select
              value={data.view_id || ''}
              id="views"
              className="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition duration-200"
              onChange={handleViewChange}
              disabled={processing}
            >
              <option value="" disabled>Выберите представление</option>
              {views.map(({ name, id }) => (
                <option key={id} value={id}>{name}</option>
              ))}
            </select>
            <InputError message={errors.view_id} className="mt-2" />
          </div>

          {selectedView && (
            <div className="space-y-4">
              <h3 className="text-md font-semibold text-gray-700">
                Настройки для {selectedView.name}
              </h3>

              {selectedView.type === 'main' && (
                <MainViewSetting
                  data={data}
                  errors={errors}
                  processing={processing}
                  handleSettingChange={handleSettingChange}
                />
              )}

              {selectedView.type === 'sizes' && (
                <SizeViewSetting
                  data={data}
                  errors={errors}
                  processing={processing}
                  handleSettingChange={handleSettingChange}
                />
              )}
            </div>
          )}

          <div className="flex justify-end space-x-4">
            <SecondaryButton onClick={closeModal}>
              Отмена
            </SecondaryButton>
            <PrimaryButton disabled={processing}>
              Сохранить настройки
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>
  );
}