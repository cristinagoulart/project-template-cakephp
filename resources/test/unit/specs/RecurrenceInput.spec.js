import RecurrenceInput from '@/components/RecurrenceInput'
import { createVM } from '../helpers/utils'

describe('RecurrenceInput.vue', function () {
  it('should render correct contents', function () {
    const vm = createVM(this,
      `<RecurrenceInput name="ScheduledJobs[recurrence]" recurrence-data=""></RecurrenceInput>`,
      { components: { RecurrenceInput } }
    )

    vm.$el.querySelector('.recurrence-container').should.exist
  })
})
