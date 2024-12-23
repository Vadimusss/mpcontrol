
import SecondaryButton from '@/Components/SecondaryButton';
import PrimaryButton from '@/Components/PrimaryButton';
import InputError from '@/Components/InputError';
import Modal from '@/Components/Modal';
import { useForm } from '@inertiajs/react';

export default function SetWorkSpaceSettingModal({ shopId, workSpace, goodLists, maxWidth, IsOpen, closeModal }) {
    const { data, setData, patch, processing, errors } = useForm({
        goodListId: null,
    });

    const submitSetWorkSpaceSetting = (e) => {
        e.preventDefault();
        patch(route('shops.workspaces.update', { shop: shopId, workspace: workSpace.id }), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        })
    }
console.log(data);
    return (
        <Modal maxWidth={maxWidth} show={IsOpen} onClose={closeModal}>
            <div className='w-full p-4'>
                <h2 className="text-lg font-medium text-gray-900 mb-5">
                    Настройки рабочей области {workSpace.name}
                </h2>
                <form onSubmit={submitSetWorkSpaceSetting} class="">
                    <div class="mb-4 w-64">
                        <label for="goodLists" class="block mb-2 text-sm font-medium text-gray-900">Выберите список товаров</label>
                        <select
                            onChange={e => setData('goodListId', e.target.value)}
                            id="goodLists"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2">
                            {goodLists && goodLists.map(({ id, name }) => <option value={id} key={id}>{name}</option>)}
                        </select>
                    </div>
                    <div class="mb-5 flex w-64 flex-col">
                        <InputError message={errors.goodListId} className="max-w-fit" />
                        <PrimaryButton className="max-w-fit mb-2" disabled={processing}>Сохранить настройки</PrimaryButton>
                        <SecondaryButton className="max-w-fit" onClick={closeModal}>
                            Отмена
                        </SecondaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    );
}