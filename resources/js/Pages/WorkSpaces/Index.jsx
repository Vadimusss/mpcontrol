import { useState, useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import AddWorkSpaceModal from '@/Pages/WorkSpaces/Components/Modals/AddWorkSpaceModal';
import WorkSpaceCard from '@/Pages/WorkSpaces/Components/WorkSpaceCard';
import { Head } from '@inertiajs/react';

export default function WorkSpaces({ auth, shop, workSpaces, goodLists, views }) {
  const [addWorkSpaceModalIsOpen, setAddWorkSpaceModalIsOpen] = useState(false);

  const availableWorkSpaces = useMemo(
    () => workSpaces.filter((workSpace) => workSpace.creator.id !== auth.user.id),
    [workSpaces]
  );

  const ownWorkSpaces = useMemo(
    () => workSpaces.filter((workSpace) => workSpace.creator.id === auth.user.id),
    [workSpaces]
  );

  const handleAddWorkSpaceButtonClick = () => {
    setAddWorkSpaceModalIsOpen(true);
  };

  const closeAddWorkSpaceModal = (() => {
    setAddWorkSpaceModalIsOpen(false);
  });

  const isGoodListsExists = goodLists.length !== 0;

  return (
    <AuthenticatedLayout
      navigation={true}
      shopId={shop.id}
      header={
        <h2 className="text-xl font-semibold leading-tight text-gray-800">
          {shop.name}
        </h2>
      }
    >
      <Head title={`Магазин ${shop.name}`} />

      <div className="max-w-2xl mx-auto">
        <div className="p-2 sm:p-3 lg:p-6 space-y-6">
          {availableWorkSpaces.length !== 0 && (
            <div>
              <h2 className="text-xl font-bold text-gray-800 mb-4">Рабочие области магазина</h2>
              <div className="space-y-4">
                {availableWorkSpaces.map((workSpace) => (
                  <WorkSpaceCard
                    auth={auth}
                    shopId={shop.id}
                    workSpace={workSpace}
                    key={workSpace.id}
                  />
                ))}
              </div>
            </div>
          )}

          {ownWorkSpaces.length !== 0 && (
            <div>
              <h2 className="text-xl font-bold text-gray-800 mb-4">Мои рабочие области</h2>
              <div className="space-y-4">
                {ownWorkSpaces.map((workSpace) => (
                  <WorkSpaceCard
                    auth={auth}
                    shopId={shop.id}
                    workSpace={workSpace}
                    goodLists={goodLists}
                    key={workSpace.id}
                    views={views}
                  />
                ))}
              </div>
            </div>
          )}

          <PrimaryButton
            onClick={handleAddWorkSpaceButtonClick}
            disabled={!isGoodListsExists}
          >
            {isGoodListsExists ? 'Добавить рабочую область' : 'Создайте список товаров'}
          </PrimaryButton>
        </div>
      </div>

      <AddWorkSpaceModal
        isOpen={addWorkSpaceModalIsOpen}
        shopId={shop.id}
        maxWidth="xl"
        goodLists={goodLists}
        views={views}
        closeModal={closeAddWorkSpaceModal}
      />
    </AuthenticatedLayout>
  );
}
